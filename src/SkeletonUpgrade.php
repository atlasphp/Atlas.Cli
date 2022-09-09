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

class SkeletonUpgrade
{
    public function __construct(
        protected Config $config,
        protected Fsio $fsio,
        protected Logger $logger
    ) {
    }

    public function __invoke() : ?int
    {
        // generate *first* to make sure all files are in place
        $skeleton = new Skeleton($this->config, $this->fsio, $this->logger);
        $code = $skeleton();

        if ($code) {
            return $code;
        }

        // now do the upgrades in place
        $this->logger->info("Upgrading skeleton data source classes.");
        $typeDirs = $this->fsio->glob("{$this->config->directory}/*");
        foreach ($typeDirs as $typeDir) {
            $type = basename($typeDir);
            $this->mapper($typeDir, $type);
            $this->events($typeDir, $type);
            $this->fields($typeDir, $type);
            $this->record($typeDir, $type);
            $this->recordSet($typeDir, $type);
            $this->relationships($typeDir, $type);
            $this->row($typeDir, $type);
            $this->select($typeDir, $type);
            $this->table($typeDir, $type);
            $this->tableEvents($typeDir, $type);
            $this->tableSelect($typeDir, $type);
        }
        $this->logger->info("Done upgrading!");

        // generate *again* to pick up related fields
        return $skeleton();
    }

    protected function mapper($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}.php", [
            '/^use Atlas\\\\Mapper\\\\Mapper;[\r\n]*/m' => '',
            '/^ \* @method .*[\r\n]*/m' => '',
            '/\/\*\*\s*\*\/[\r\n]*/m' => '',
            '/ extends Mapper(.*)/' => " extends _generated\\{$type}$1_",
        ]);
    }

    protected function events($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}Events.php", [
            '/^use Atlas\\\\Mapper\\\\MapperEvents;[\r\n]*/m' => '',
            '/ extends MapperEvents(.*)/' => " extends _generated\\{$type}Events$1_",
        ]);
    }

    protected function fields($typeDir, $type)
    {
        $file = "{$typeDir}/{$type}Fields.php";
        $this->fsio->unlink("{$typeDir}/{$type}Fields.php");
        $this->logger->info(" Deleted: {$file}");
    }

    protected function record($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}Record.php", [
            '/^use Atlas\\\\Mapper\\\\Record;[\r\n]*/m' => '',
            '/^ \* @method .*[\r\n]*/m' => '',
            '/\/\*\*\s*\*\/[\r\n]*/m' => '',
            '/ extends Record(.*)/' => " extends _generated\\{$type}Record$1_",
            "/^    use {$type}Fields;[\r\n]*/m" => '',
        ]);
    }

    protected function recordSet($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}RecordSet.php", [
            '/^use Atlas\\\\Mapper\\\\RecordSet;[\r\n]*/m' => '',
            '/^ \* @method .*[\r\n]*/m' => '',
            '/\/\*\*\s*\*\/[\r\n]*/m' => '',
            '/ extends RecordSet(.*)/' => " extends _generated\\{$type}RecordSet$1_",
        ]);
    }

    protected function relationships($typeDir, $type)
    {
        $file = "{$typeDir}/{$type}Relationships.php";
        if (! $this->fsio->isFile($file)) {
            $this->logger->info(" Skipped: {$file} (not found)");
            return;
        }

        $this->rewrite("{$typeDir}/{$type}Relationships.php", [
            '/ extends MapperRelationships/' => " extends \Atlas\Cli\SkeletonUpgrade\MapperRelationships",
            '/    protected function define/' => '    public function define',
        ]);

        // make sure these classes are loaded before the Relationships class
        class_exists(SkeletonUpgrade\MapperRelationship::CLASS);
        class_exists(SkeletonUpgrade\MapperRelationships::CLASS);

        // now get the Relationships class
        require "{$typeDir}/{$type}Relationships.php";
        $classes = get_declared_classes();
        $class = end($classes);
        $mapperRelationships = new $class();
        $mapperRelationships->define();

        $this->rewrite("{$typeDir}/{$type}Related.php", [
            '/use Atlas\\\\Mapper\\\\Define;/' => '$0' . PHP_EOL . $mapperRelationships->imports(),
            '/\{[\r\n]+\}/m' => '{' . PHP_EOL . $mapperRelationships->properties() . PHP_EOL . '}',
        ]);

        $this->fsio->unlink($file);
        $this->logger->info(" Deleted: {$file}");
    }

    protected function row($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}Row.php", [
            '/^use Atlas\\\\Table\\\\Row;[\r\n]*/m' => '',
            '/^ \* @property .*[\r\n]*/m' => '',
            '/\/\*\*\s*\*\/[\r\n]*/m' => '',
            '/ extends Row(.*)/' => " extends _generated\\{$type}Row$1_",
            '/^    protected \$cols = \[.*?    \];[\r\n]*/ms' => '',
        ]);
    }

    protected function select($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}Select.php", [
            '/^use Atlas\\\\Mapper\\\\MapperSelect;[\r\n]*/m' => '',
            '/^ \* @method .*[\r\n]*/m' => '',
            '/\/\*\*\s*\*\/[\r\n]*/m' => '',
            '/ extends MapperSelect(.*)/' => " extends _generated\\{$type}Select$1_",
        ]);
    }

    protected function table($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}Table.php", [
            '/^use Atlas\\\\Table\\\\Table;[\r\n]*/m' => '',
            '/^ \* @method .*[\r\n]*/m' => '',
            '/\/\*\*\s*\*\/[\r\n]*/m' => '',
            '/ extends Table(.*)/' => " extends _generated\\{$type}Table$1_",
            '/^    const DRIVER = .*[\r\n]*/m' => '',
            '/^    const NAME = .*[\r\n]*/m' => '',
            '/^    const COLUMNS = \[.*?    \];[\r\n]*/ms' => '',
            '/^    const COLUMN_NAMES = \[.*?    \];[\r\n]*/ms' => '',
            '/^    const COLUMN_DEFAULTS = \[.*?    \];[\r\n]*/ms' => '',
            '/^    const PRIMARY_KEY = \[.*?    \];[\r\n]*/ms' => '',
            '/^    const AUTOINC_COLUMN = .*[\r\n]*/m' => '',
            '/^    const AUTOINC_SEQUENCE = .*[\r\n]*/m' => '',
        ]);
    }

    protected function tableEvents($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}TableEvents.php", [
            '/ extends TableEvents(.*)/' => " extends _generated\\{$type}TableEvents$1_",
        ]);
    }

    protected function tableSelect($typeDir, $type)
    {
        $this->rewrite("{$typeDir}/{$type}TableSelect.php", [
            '/^use Atlas\\\\Table\\\\TableSelect;[\r\n]*/m' => '',
            '/^ \* @method .*[\r\n]*/m' => '',
            '/\/\*\*\s*\*\/[\r\n]*/m' => '',
            '/ extends TableSelect(.*)/' => " extends _generated\\{$type}TableSelect$1_",
        ]);
    }

    protected function rewrite(string $file, array $findReplace)
    {
        if (! $this->fsio->isFile($file)) {
            $this->logger->info("-Failure: {$file} (not found)");
            return;
        }

        $code = $this->fsio->get($file);

        foreach ($findReplace as $find => $replace) {
            $code = preg_replace($find, $replace, $code);
        }

        $this->fsio->put($file, $code);
        $this->logger->info('+Success: {$file} (upgraded)');
    }
}
