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
use Atlas\Pdo\Connection;
use ReflectionClass;
use Throwable;

class Skeleton
{
    protected Connection $connection;

    protected Info $info;

    protected Transform $transform;

    protected string $directory;

    protected array $templates = [];

    protected string $namespace;

    protected array $types = [];

    public function __construct(
        protected Config $config,
        protected Fsio $fsio,
        protected Logger $logger
    ) {
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
        } catch (Throwable $e) {
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
        $this->namespace = trim($this->config->namespace, '\\');
        return null;
    }

    protected function setTemplates() : ?int
    {
        $names = [
            '_generated/Type_',
            '_generated/TypeEvents_',
            '_generated/TypeRecord_',
            '_generated/TypeRecordSet_',
            '_generated/TypeRelated_',
            '_generated/TypeRow_',
            '_generated/TypeSelect_',
            '_generated/TypeTable_',
            '_generated/TypeTableEvents_',
            '_generated/TypeTableSelect_',
            'Type',
            'TypeEvents',
            'TypeRecord',
            'TypeRecordSet',
            'TypeRelated',
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
                    "{$dir}/{$name}.tpl.php"
                );

                if ($this->fsio->isFile($file)) {
                    $this->templates[$name] = $file;
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

        foreach ($this->types as $type => $info) {
            $this->putFiles($type, ...$info);
        }

        $this->logger->info("Done generating!");
        return null;
    }

    protected function putFiles(string $type, string $table, array $columns, ?string $sequence) : void
    {
        $dir = "{$this->directory}/{$type}";
        $this->mkdir("{$dir}/_generated");

        $vars = [
            'COLDEFS' => $this->getColDefs($columns),
            'COLUMNS' => $columns,
            'DRIVER' => $this->connection->getDriverName(),
            'NAMESPACE' => $this->namespace,
            'RELATED' => $this->getRelated($type),
            'SEQUENCE' => $sequence,
            'TABLE' => $table,
            'TYPE' => $type,
        ];

        foreach ($this->templates as $name => $tpl) {
            $code = $this->render($tpl, $vars);
            $name = str_replace('Type', $type, $name);
            $file = "{$dir}/{$name}.php";
            $this->putFile($file, $code);
        }
    }

    protected function getColDefs(array $columns) : array
    {
        $coldefs = [];

        foreach ($columns as $col) {
            $coldef = $col['type'];
            $unsigned = '';

            if (substr(strtoupper($coldef), -9) == ' UNSIGNED') {
                $unsigned = substr($coldef, -9);
                $coldef = substr($coldef, 0, -9);
            }

            if ($col['size'] !== null) {
                $coldef .= "({$col['size']}";
                if ($col['scale'] !== null) {
                    $coldef .= ",{$col['scale']}";
                }
                $coldef .= ')';
            }

            $coldef .= $unsigned;

            if ($col['notnull'] === true) {
                $coldef .= ' NOT NULL';
            }

            $coldefs[$col['name']] = $coldef;
        }

        return $coldefs;
    }

    protected function getRelated(string $type) : array
    {
        $related = [];
        $class = "{$this->namespace}\\{$type}\\{$type}Related";

        if (! class_exists($class)) {
            return $related;
        }

        $props = (new ReflectionClass($class))->getProperties();

        foreach ($props as $prop) {
            $type = (string) $prop->getType();
            $nullable = substr($type, 0, 1) === '?';

            if ($nullable) {
                $type = '?\\' . substr($type, 1);
            } else {
                $type = '\\' . $type;
            }

            $related[$prop->getName()] = $type;
        }

        return $related;
    }

    protected function mkdir(string $dir) : ?int
    {
        if ($this->fsio->isDir($dir)) {
            $this->logger->info(" Skipped: mkdir {$dir} (already exists)");
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

    protected function putFile(string $file, string $code) : void
    {
        $overwrite = substr($file, -5) == '_.php';

        if ($this->fsio->isFile($file) && ! $overwrite) {
            $this->logger->info(" Skipped: {$file} (already exists)");
            return;
        }

        $this->fsio->put($file, $code);
        $this->logger->info("+Success: {$file} (generated)");
    }

    protected function render(string $tpl, array $vars) : string
    {
        extract($vars);
        ob_start();
        require $tpl;
        return "<?php" . PHP_EOL . ob_get_clean();
    }
}
