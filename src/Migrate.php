<?php
namespace Atlas\Cli;

use Atlas\Pdo\Connection;
use PDO;

class Migrate
{
    protected $pdo;

    protected $fsio;

    protected $logger;

    protected $table;

    protected $column;

    protected $directory;

    protected $namespace;

    protected $connection;

    public function __construct(
        Config $config,
        Fsio $fsio,
        Logger $logger,
        callable $migrationFactory = null
    ) {
        $this->fsio = $fsio;
        $this->logger = $logger;
        if ($migrationFactory === null) {
            $migrationFactory = function ($class) {
                return new $class();
            };
        };
        $this->migrationFactory = $migrationFactory;

        // if missing, blow up
        $this->pdo = $config->pdo;
        $this->table = $config->migration['table'];
        $this->column = $config->migration['column'];
        $this->directory = $config->migration['directory'];
        $this->namespace = $config->migration['namespace'];
    }

    public function up($to = null) : int
    {
        $current = $this->fetchCurrentVersion();
        $versions = $this->getVersions();

        if ($to === null) {
            $to = end($versions);
            reset($versions);
        }

        if (! in_array($to, $versions)) {
            $this->logger->error("Version {$to} not recognized.");
            return 1;
        }

        if ($current >= $to) {
            $this->logger->error(
                "Current version {$current} is already at or later than target version {$to}."
            );
            return 1;
        }

        $this->logger->info("Migrating up from {$current} to {$to}.");

        while (! empty($versions)) {
            $version = array_shift($versions);

            if ($version <= $current) {
                continue;
            }

            if ($version > $to) {
                break;
            }

            if (! $this->apply($current, 'up', 'to', $version)) {
                return 1;
            }
        }

        $this->logger->info("Now at {$to}.");
        return 0;
    }

    public function down($to = null) : int
    {
        $current = $this->fetchCurrentVersion();
        $versions = $this->getVersions();

        if ($to === null) {
            $to = reset($versions);
        }

        if (! in_array($to, $versions)) {
            $this->logger->error("Version {$to} not recognized.");
            return 1;
        }

        if ($current <= $to) {
            $this->logger->error(
                "Current version {$current} is already at or earlier than target version {$to}."
            );
            return 1;
        }

        $this->logger->info("Migrating down from {$current} to {$to}.");

        while (! empty($versions)) {
            $version = array_pop($versions);

            if ($version > $current) {
                continue;
            }

            if ($version <= $to) {
                break;
            }

            if (! $this->apply($current, 'down', 'from', $version)) {
                return 1;
            }
        }

        $this->logger->info("Now at {$to}.");
        return 0;
    }

    protected function apply(
        string $current,
        string $method,
        string $dir,
        string $version
    ) : bool
    {
        try {
            $this->getConnection()->beginTransaction();
            $migration = $this->newMigration($version);
            $migration->$method($this->connection);
            $this->updateVersion($version);
            $this->getConnection()->commit();
            $this->logger->info("Migration {$method} {$dir} {$version} committed!");
            return true;
        } catch (Exception $e) {
            $this->getConnection()->rollBack();
            $this->logger->error("Migration {$method} {$dir} {$version} failed.");
            $this->logger->error($e->__toString());
            return false;
        }
    }

    protected function newConnection()
    {
        $pdo = $this->pdo;
        $connection = Connection::new(...$pdo);
        $errmode = $connection->getAttribute(PDO::ATTR_ERRMODE);
        if ($errmode != PDO::ERRMODE_EXCEPTION) {
            throw new Exception('connection must use ERRMODE_EXCEPTION for migrations.');
        }
        return $connection;
    }

    protected function getConnection()
    {
        if ($this->connection === null) {
            $this->connection = $this->newConnection();
        }

        return $this->connection;
    }

    protected function getVersions()
    {
        $versions = [];
        $pattern = $this->directory . DIRECTORY_SEPARATOR . '*.php';
        $files = glob($pattern);

        foreach ($files as $file) {
            $versions[] = substr(basename($file), 0, -4);
        }

        return $versions;
    }

    protected function newMigration($version) : Migration
    {
        $class = $this->namespace . '\\' . $version;
        return ($this->migrationFactory)($class);
    }

    protected function fetchCurrentVersion()
    {
        return $this->getConnection()->fetchValue(
            "SELECT {$this->column} FROM {$this->table}"
        );
    }

    protected function updateVersion($version) : void
    {
        $this->getConnection()->perform(
            "UPDATE {$this->table} SET {$this->column} = :version",
            ['version' => $version]
        );
    }
}
