<?php
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\Fsio;
use Atlas\Cli\Logger;
use Aura\Cli\Status;
use Aura\SqlSchema\ColumnFactory;
use Exception;
use PDO;

class Skeleton
{
    protected $logger;
    protected $fsio;
    protected $input;
    protected $type;
    protected $subdir;
    protected $vars;
    protected $templates;

    public function __construct(Fsio $fsio, Logger $logger)
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
    }

    public function __invoke(SkeletonInput $input)
    {
        $this->input = $input;

        $methods = [
            'filterInput',
            'setType',
            'setSubdir',
            'setVars',
            'setTemplates',
            'createClasses',
        ];

        foreach ($methods as $method) {
            $exit = $this->$method();
            if ($exit) {
                return $exit;
            }
        }
    }

    protected function filterInput()
    {
        if (! $this->input->dir) {
            $this->logger->error('Please provide a target directory.');
            return Status::USAGE;
        }

        if (! $this->input->namespace) {
            $this->logger->error('Please provide a namespace; e.g. App\\\\DataSource\\\\Type.');
            return Status::USAGE;
        }

        $conn = $this->input->conn;
        $table = $this->input->table;
        if ($conn && ! $table) {
            $this->logger->error("Please provide a table name to use with the connection.");
            return Status::USAGE;
        }

        if (! $conn && $table) {
            $this->logger->error("Please provide a connection to use with the table name.");
            return Status::USAGE;
        }
    }

    protected function setType()
    {
        $namespace = $this->input->namespace;
        $lastNsPos = (int) strrpos($namespace, '\\');
        $this->type = ltrim(substr($namespace, $lastNsPos), '\\');
    }

    protected function setSubdir()
    {
        $this->subdir =
            $this->input->dir . DIRECTORY_SEPARATOR .
            $this->type . DIRECTORY_SEPARATOR;
    }

    protected function setVars()
    {
        $this->vars = [
            '{NAMESPACE}' => $this->input->namespace,
            '{TYPE}' => $this->type,
        ];

        if (! $this->input->conn) {
            return;
        }

        $table = $this->input->table;
        $schema = $this->newSchema();
        $tables = $schema->fetchTableList();
        if (! in_array($table, $tables)) {
            $this->logger->error("-Failure: table '{$table}' not found.");
            return Status::FAILURE;
        }

        $primary = null;
        $autoinc = 'false';
        $list = [];
        $info = '';
        foreach ($schema->fetchTableCols($table) as $col) {
            $list[$col->name] = $col->default;
            if ($col->primary) {
                $primary = $col->name;
            }
            if ($col->autoinc) {
                $autoinc = 'true';
            }
            $info .= "            '{$col->name}' => (object) " . var_export([
                'name' => $col->name,
                'type' => $col->type,
                'size' => $col->size,
                'scale' => $col->scale,
                'notnull' => $col->notnull,
                'default' => $col->default,
                'autoinc' => $col->autoinc,
                'primary' => $col->primary,
            ], true) . ',' . PHP_EOL;
        }

        $repl = [
            ' => (object) array (' . PHP_EOL => ' => (object) [' . PHP_EOL,
            PHP_EOL . "  '" => PHP_EOL . "                '",
            PHP_EOL . ")" => PHP_EOL . "            ]",
            " => NULL," . PHP_EOL => " => null," . PHP_EOL,
        ];
        $info = str_replace(array_keys($repl), array_values($repl), $info);
        $info = '[' . PHP_EOL . $info . '        ]';

        $cols = "[" . PHP_EOL;
        $default = "[" . PHP_EOL;
        foreach ($list as $col => $val) {
            $val = ($val === null) ? 'null' : var_export($val, true);
            $cols .= "            '$col'," . PHP_EOL;
            $default .= "            '$col' => $val," . PHP_EOL;
        }
        $cols .= "        ]";
        $default .= "        ]";

        $this->vars += [
            '{TABLE}' => "'$table'",
            '{COLS}' => $cols,
            '{DEFAULT}' => $default,
            '{AUTOINC}' => $autoinc,
            '{PRIMARY}' => "'$primary'",
            '{INFO}' => $info,
        ];
    }

    protected function newSchema()
    {
        $conn = $this->input->conn;

        try {
            $pdo = new PDO(...$conn);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return Status::UNAVAILABLE;
        }

        $dsn = $conn[0];
        $pos = strpos($dsn, ':');
        $db = ucfirst(strtolower(substr($dsn, 0, $pos)));
        $schemaClass = "Aura\\SqlSchema\\{$db}Schema";
        return new $schemaClass($pdo, new ColumnFactory());
    }

    protected function setTemplates()
    {
        $classes = [];
        if ($this->input->conn) {
            $classes[] = 'Table';
        }
        $classes[] = 'Mapper';
        if ($this->input->full) {
            $classes[] = 'Plugin';
            $classes[] = 'Record';
            $classes[] = 'RecordSet';
        }

        // look in custom template dir first, then default location
        $dirs = [];
        if ($this->input->tpl) {
            $dirs[] = $this->input->tpl;
        }
        $dirs[] = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'templates';

        foreach ($classes as $class) {
            foreach ($dirs as $dir) {
                $file = $dir. DIRECTORY_SEPARATOR . $class . '.tpl';
                if ($this->fsio->isFile($file)) {
                    $this->templates[$class] = $this->fsio->get($file);
                }
            }
        }
    }

    protected function createClasses()
    {
        $this->logger->info("Generating skeleton data source classes.");
        $this->logger->info("Namespace: " . $this->input->namespace);
        $this->logger->info("Directory: " . $this->input->dir);
        $this->mkSubDir();
        foreach ($this->templates as $class => $template) {
            $this->createClass($class, $template);
        }
        $this->logger->info("Done!");
    }

    protected function mkSubDir()
    {
        if ($this->fsio->isDir($this->subdir)) {
            $this->logger->info(" Skipped: mkdir {$this->subdir}");
            return;
        }

        try {
            $this->fsio->mkdir($this->subdir, 0755, true);
        } catch (Exception $e) {
            $this->logger->error("-Failure: mkdir {$this->subdir}");
            return Status::CANTCREAT;
        }

        $this->logger->info("+Success: mkdir {$this->subdir}");
    }

    protected function createClass($class, $template)
    {
        $file = $this->subdir . $this->type . $class . '.php';
        if ($class !== 'Table' && $this->fsio->isFile($file)) {
            $this->logger->info(" Skipped: $file");
            return;
        }

        $code = strtr($template, $this->vars);
        $this->fsio->put($file, $code);
        $this->logger->info("+Success: $file");
    }
}
