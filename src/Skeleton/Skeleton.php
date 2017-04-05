<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\Fsio;
use Atlas\Cli\Logger;
use Aura\Cli\Status;
use Aura\SqlSchema\ColumnFactory;
use Exception;
use PDO;

/**
 *
 * Builds the skeleton for a mapper.
 *
 * @package atlas/cli
 *
 */
class Skeleton
{
    /**
     *
     * Logger for output.
     *
     * @var Logger
     *
     */
    protected $logger;

    /**
     *
     * Filesysyem I/O object.
     *
     * @var Fsio
     *
     */
    protected $fsio;

    /**
     *
     * Command input values.
     *
     * @var SkeletonInput
     *
     */
    protected $input;

    /**
     *
     * The "type" (namespace) for the skeleton being created.
     *
     * @var string
     *
     */
    protected $type;

    /**
     *
     * The subdirectory where skeleton files will go for the "type".
     *
     * @var string
     *
     */
    protected $subdir;

    /**
     *
     * Variables to be interpolated into the templates.
     *
     * @var array
     *
     */
    protected $vars;

    /**
     *
     * The templates for the different skeleton classes.
     *
     * @var array
     *
     */
    protected $templates;

    /**
     *
     * Database connection information.
     *
     * @var array
     *
     */
    protected $conn = [];

    /**
     *
     * Constructor.
     *
     * @param Fsio $fsio A filesystem i/o handler.
     *
     * @param Logger $logger The output logger.
     *
     * @param array $conn Database connection information.
     *
     */
    public function __construct(Fsio $fsio, Logger $logger, array $conn = [])
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
        $this->conn = $conn;
    }

    /**
     *
     * Invokes the skeleton-building process.
     *
     * @param SkeletonInput $input Input from the skeleton command.
     *
     * @return int An exit status for the calling code.
     *
     */
    public function __invoke(SkeletonInput $input)
    {
        $this->input = $input;

        $methods = [
            'setConn',
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

    /**
     *
     * Sets the connection information.
     *
     * @return int|null An exit status code, or null if all is well.
     *
     */
    protected function setConn()
    {
        if ($this->input->conn) {
            $this->conn = $this->input->conn;
        }
    }

    /**
     *
     * Filters the command input.
     *
     * @return null
     *
     */
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

        if ($this->input->table && ! $this->conn) {
            $this->logger->error("Please provide a connection to use with the table name.");
            return Status::USAGE;
        }
    }

    /**
     *
     * Sets the "type" for the skeleton classes being generated.
     *
     * @return null
     *
     */
    protected function setType()
    {
        $namespace = $this->input->namespace;
        $lastNsPos = (int) strrpos($namespace, '\\');
        $this->type = ltrim(substr($namespace, $lastNsPos), '\\');
    }

    /**
     *
     * Set the subdirectory where the skeleton files will go.
     *
     * @return null
     *
     */
    protected function setSubdir()
    {
        $this->subdir =
            $this->input->dir . DIRECTORY_SEPARATOR .
            $this->type . DIRECTORY_SEPARATOR;
    }

    /**
     *
     * Sets the variables to be interpolated into the the templates.
     *
     * @return int|null An exit code, or null if all is well.
     *
     */
    protected function setVars()
    {
        $this->vars = [
            '{NAMESPACE}' => $this->input->namespace,
            '{TYPE}' => $this->type,
        ];

        $table = $this->input->table;
        if (! $table) {
            return;
        }

        $schema = $this->newSchema();
        $tables = $schema->fetchTableList();
        if (! in_array($table, $tables)) {
            $this->logger->error("-Failure: table '{$table}' not found.");
            return Status::FAILURE;
        }

        $primary = '';
        $autoinc = "''";
        $list = [];
        $info = '';
        foreach ($schema->fetchTableCols($table) as $col) {
            $list[$col->name] = $col->default;
            if ($col->primary) {
                $primary .= "            '{$col->name}'," . PHP_EOL;
            }
            if ($col->autoinc) {
                $autoinc = "'$col->name'";
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

        $primary = '[' . PHP_EOL . $primary . '        ]';

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
            '{PRIMARY}' => $primary,
            '{INFO}' => $info,
        ];
    }

    /**
     *
     * Gets a new SQL schema description object.
     *
     * @return int|object An exit code, or an SQL schema description object.
     *
     */
    protected function newSchema()
    {
        $conn = $this->conn;

        try {
            $pdo = new PDO(...$conn);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return Status::UNAVAILABLE;
        }

        $db = ucfirst($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
        $schemaClass = "Aura\\SqlSchema\\{$db}Schema";
        return new $schemaClass($pdo, new ColumnFactory());
    }

    /**
     *
     * Gets and retains the templates.
     *
     * @return null
     *
     */
    protected function setTemplates()
    {
        $classes = [];
        if ($this->input->table) {
            $classes[] = 'Table';
        }
        $classes[] = 'Mapper';
        if ($this->input->full) {
            $classes[] = 'MapperEvents';
            $classes[] = 'Record';
            $classes[] = 'RecordSet';
            $classes[] = 'TableEvents';
        }

        // look in custom template dir first, then default location
        $dirs = [
            dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'templates'
        ];
        if ($this->input->tpl) {
            $dirs[] = $this->input->tpl;
        }

        foreach ($classes as $class) {
            foreach ($dirs as $dir) {
                $file = $dir. DIRECTORY_SEPARATOR . $class . '.tpl';
                if ($this->fsio->isFile($file)) {
                    $this->templates[$class] = $this->fsio->get($file);
                }
            }
        }
    }

    /**
     *
     * Actually writes all the skeleton class files to the filesystem.
     *
     * @return null
     *
     */
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

    /**
     *
     * Actually creates the subdirectory for the skeleton files.
     *
     * @return int|null An exit code, or null if all is well.
     *
     */
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

    /**
     *
     * Actually writes a single skeleton class file to the filesystem.
     *
     * @param string $class The class name.
     *
     * @param string $template The template text.
     *
     * @return null
     *
     */
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
