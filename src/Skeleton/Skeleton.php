<?php
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\Fsio;
use Aura\Cli\Status;
use Aura\Cli\Stdio;
use Aura\SqlSchema\ColumnFactory;
use Exception;
use PDO;

// convert to logging instead of stdio
class Skeleton
{
    protected $stdio;
    protected $fsio;
    protected $input;
    protected $type;
    protected $subdir;
    protected $vars;
    protected $templates;

    public function __construct(Fsio $fsio, Stdio $stdio)
    {
        $this->fsio = $fsio;
        $this->stdio = $stdio;
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
            $this->stdio->errln('Please provide a target directory.');
            return Status::USAGE;
        }

        if (! $this->input->namespace) {
            $this->stdio->errln('Please provide a namespace; e.g. App\\\\DataSource\\\\Type.');
            return Status::USAGE;
        }

        $conn = $this->input->conn;
        if ($conn && ! is_array($conn)) {
            $this->stdio->errln("Connection config is not an array of PDO parameters.");
            return Status::USAGE;
        }

        if (! $conn && $this->input->table) {
            // notice, not error
            $this->stdio->errln("Ignoring table without connection.");
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
        $dir = rtrim($this->input->dir, DIRECTORY_SEPARATOR);
        $this->subdir =
            $dir . DIRECTORY_SEPARATOR .
            $this->type . DIRECTORY_SEPARATOR;

        if ($this->fsio->isDir($this->subdir)) {
            $this->stdio->outln(" Skipped: mkdir {$this->subdir}");
            return;
        }

        try {
            $this->fsio->mkdir($this->subdir, 0755, true);
        } catch (Exception $e) {
            $this->stdio->errln("-Failure: mkdir {$this->subdir}");
            return Status::CANTCREAT;
        }

        $this->stdio->outln("+Success: mkdir {$this->subdir}");
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

        $table = trim($this->input->table);
        if (! $table) {
            $table = strtolower($this->type);
        }

        $schema = $this->newSchema();
        $tables = $schema->fetchTableList();
        if (! in_array($table, $tables)) {
            $this->stdio->errln("-Failure: table '{$table}' not found.");
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
            $this->stdio->errln($e->getMessage());
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

        $dir = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'templates';
        foreach ($classes as $class) {
            $file = $dir. DIRECTORY_SEPARATOR . $class . '.tpl';
            $this->templates[$class] = $this->fsio->get($file);
        }
    }

    protected function createClasses()
    {
        foreach ($this->templates as $class => $template) {
            $this->createClass($class, $template);
        }
    }

    protected function createClass($class, $template)
    {
        $file = $this->subdir . $this->type . $class . '.php';
        if ($class !== 'Table' && $this->fsio->isFile($file)) {
            $this->stdio->outln(" Skipped: $file");
            return;
        }

        $code = strtr($template, $this->vars);
        $this->fsio->put($file, $code);
        $this->stdio->outln("+Success: $file");
    }
}
