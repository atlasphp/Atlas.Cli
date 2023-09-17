<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Cli;

use Atlas\Info\Info;
use Atlas\Mapper\Relationship\ManyToMany;
use Atlas\Pdo\Connection;
use Atlas\Mapper\MapperLocator;
use Atlas\Mapper\Relationship\OneToMany;
use Atlas\Mapper\Relationship\ManyToOneVariant;
use ReflectionClass;

class Skeleton
{
    protected $config;
    protected $fsio;
    protected $logger;

    protected $connection;
    protected $info;
    protected $directory;
    protected $templates = [];
    protected $namespace;
    protected $types = [];
    protected $transform;

    public function __construct(Config $config, Fsio $fsio, Logger $logger)
    {
        $this->config = $config;
        $this->fsio = $fsio;
        $this->logger = $logger;
    }

    public function __invoke() : ?int
    {
        try {
            return $this->setInfo()
                ?? $this->setDirectory()
                ?? $this->setNamespace()
                ?? $this->setTemplates()
                ?? $this->setTransform()
                ?? $this->getTypes()
                ?? $this->putTypes();
        } catch (\Exception $e) {
            $this->logger->error((string) $e);
            return 1;
        }
    }

    protected function setInfo() : ?int
    {
        $pdo = $this->config->pdo;
        $this->connection = Connection::new(...$pdo);
        $this->info = Info::new($this->connection);
        return null;
    }

    protected function setDirectory() : ?int
    {
        $this->directory = $this->config->directory;
        if (! $this->fsio->isDir($this->directory)) {
            $this->logger->error("-Directory {$this->config->directory} not found.");
            return 1;
        }
        return null;
    }

    protected function setNamespace() : ?int
    {
        $this->namespace = rtrim($this->config->namespace, '\\');
        return null;
    }

    protected function setTemplates() : ?int
    {
        $names = [
            'Type',
            'TypeEvents',
            'TypeFields',
            'TypeRecord',
            'TypeRecordSet',
            'TypeRelationships',
            'TypeRow',
            'TypeSelect',
            'TypeTable',
            'TypeTableEvents',
            'TypeTableSelect',
        ];

        $dirs = [
            dirname(__DIR__) . '/resources/templates',
            $this->config->templates,
        ];

        foreach ($names as $name) {
            foreach ($dirs as $dir) {
                $file = str_replace(
                    '/',
                    DIRECTORY_SEPARATOR,
                    "{$dir}/{$name}.tpl"
                );
                if ($this->fsio->isFile($file)) {
                    $this->templates[$name] = $this->fsio->get($file);
                }
            }
        }

        return null;
    }

    public function setTransform() : ?int
    {
        $this->transform = $this->config->transform;
        if (! is_callable($this->transform)) {
            $this->logger->error("Config key 'transform' is not callable.");
            return 1;
        }

        return null;
    }

    protected function getTypes() : ?int
    {
        $tables = $this->info->fetchTableNames();
        foreach ($tables as $table) {
            $type = ($this->transform)($table);
            if ($type === null) {
                continue;
            }
            $this->types[$type] = [
                $table,
                $this->info->fetchColumns($table),
                $this->info->fetchAutoincSequence($table),
            ];
        }

        return null;
    }

    protected function putTypes() : ?int
    {
        $this->logger->info("Generating skeleton data source classes.");
        $this->logger->info("Namespace: " . $this->config->namespace);
        $this->logger->info("Directory: " . $this->directory);
        $this->logger->info("Done!");
        foreach ($this->types as $type => $info) {
            $this->putFiles($type, ...$info);
        }

        return null;
    }

    protected function putFiles(string $type, string $table, array $columns, $sequence) : void
    {
        $dir = "{$this->directory}/{$type}";
        $this->mkdir($dir);
        $vars = $this->getVars($type, $table, $columns, $sequence);
        foreach ($this->templates as $name => $code) {
            $name = str_replace('Type', $type, $name);
            $file = "{$dir}/{$name}.php";
            $this->putFile($file, $code, $vars);
        }
    }

    protected function getVars(string $type, string $table, array $columns, $sequence) : array
    {
        $primary = '';
        $autoinc = 'null';
        $list = [];
        $info = '';
        $props = '';

        foreach ($columns as $col) {
            $list[$col['name']] = $col['default'];
            if ($col['primary']) {
                $primary .= "        '{$col['name']}'," . PHP_EOL;
            }
            if ($col['autoinc']) {
                $autoinc = "'{$col['name']}'";
            }
            $info .= "        '{$col['name']}' => " . var_export($col, true) . ',' . PHP_EOL;


            $coltype = $col['type'];
            $unsigned = '';
            if (substr(strtoupper($coltype), -9) == ' UNSIGNED') {
                $unsigned = substr($coltype, -9);
                $coltype = substr($coltype, 0, -9);
            }

            $props .= " * @property mixed \${$col['name']} {$coltype}";
            if ($col['size'] !== null) {
                $props .= "({$col['size']}";
                if ($col['scale'] !== null) {
                    $props .= ",{$col['scale']}";
                }
                $props .= ')';
            }

            $props .= $unsigned;

            if ($col['notnull'] === true) {
                $props .= ' NOT NULL';
            }
            $props .= PHP_EOL;
        }

        $primary = '[' . PHP_EOL . $primary . '    ]';

        $repl = [
            ' => array (' . PHP_EOL => ' => [' . PHP_EOL,
            PHP_EOL . "  '" => PHP_EOL . "            '",
            PHP_EOL . ")" => PHP_EOL . "        ]",
            " => NULL," . PHP_EOL => " => null," . PHP_EOL,
        ];

        $info = str_replace(
            array_keys($repl),
            array_values($repl),
            $info
        );

        $info = '[' . PHP_EOL . $info . '    ]';

        $cols = "[" . PHP_EOL;
        $default = "[" . PHP_EOL;
        foreach ($list as $col => $val) {
            $val = ($val === null) ? 'null' : var_export($val, true);
            $cols .= "        '$col'," . PHP_EOL;
            $default .= "        '$col' => $val," . PHP_EOL;
        }
        $cols .= "    ]";
        $default .= "    ]";

        $fields = $props;
        $this->setRelatedFields($type, $fields);

        $driver = $this->connection->getDriverName();

        return [
            '{NAMESPACE}' => $this->namespace,
            '{TYPE}' => $type,
            '{DRIVER}' => "'{$driver}'",
            '{NAME}' => "'{$table}'",
            '{COLUMN_NAMES}' => $cols,
            '{COLUMN_DEFAULTS}' => $default,
            '{AUTOINC_COLUMN}' => $autoinc,
            '{PRIMARY_KEY}' => $primary,
            '{COLUMNS}' => $info,
            '{AUTOINC_SEQUENCE}' => ($sequence === null) ? 'null' : "'{$sequence}'",
            '{PROPERTIES}' => rtrim($props),
            '{FIELDS}' => rtrim($fields),
        ];
    }

    protected function setRelatedFields(string $type, string &$fields) : void
    {
        $class = "{$this->namespace}\\$type\\$type";
        if (! class_exists($class)) {
            return;
        }

        $mappers = MapperLocator::new($this->connection);
        $mapper = $mappers->get($class);
        $rels = $mapper->getRelationships();

        $rclass = new ReflectionClass(get_class($rels));
        $rprop = $rclass->getProperty('relationships');
        $rprop->setAccessible(true);
        $defs = $rprop->getValue($rels);

        foreach ($defs as $name => $def) {
            $rclass = new ReflectionClass(get_class($def));
            $rprop = $rclass->getProperty('foreignMapperClass');
            $rprop->setAccessible(true);
            $foreignMapperClass = $rprop->getValue($def);
            switch (true) {
                case $def instanceof OneToMany:
                case $def instanceof ManyToMany:
                    $type = "null|\\{$foreignMapperClass}RecordSet";
                    break;
                case $def instanceof ManyToOneVariant:
                    $type = "null|false|\Atlas\Mapper\Record";
                    $name .= " (variant)";
                    break;
                default:
                    $type = "null|false|\\{$foreignMapperClass}Record";
                    break;
            }
            $fields .= " * @property {$type} \${$name}" . PHP_EOL;
        }
    }

    protected function mkdir(string $dir) : ?int
    {
        if ($this->fsio->isDir($dir)) {
            $this->logger->info(" Skipped: mkdir {$dir}");
            return null;
        }

        try {
            $this->fsio->mkdir($dir, 0755, true);
        } catch (Exception $e) {
            $this->logger->error("-Failure: mkdir {$dir}");
            return 1;
        }

        $this->logger->info("+Success: mkdir {$dir}");
        return null;
    }

    protected function putFile(string $file, string $code, array $vars)
    {
        $overwrite = substr($file, -10) == 'Fields.php'
            || substr($file, -7) == 'Row.php'
            || substr($file, -9) == 'Table.php';

        if ($this->fsio->isFile($file) && ! $overwrite) {
            $this->logger->info(" Skipped: $file");
            return;
        }

        $code = strtr($code, $vars);
        $this->fsio->put($file, $code);
        $this->logger->info("+Success: $file");
    }
}
