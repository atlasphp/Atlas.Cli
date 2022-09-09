<?php
namespace Atlas\Cli;

use Atlas\Testing\DataSourceFixture;

/**
 * @runTestsInSeparateProcesses
 */
class SkeletonTest extends \PHPUnit\Framework\TestCase
{
    protected $fsio;
    protected $logger;
    protected $stdout;
    protected $factory;
    protected $connection;

    protected function setUp() : void
    {
        $this->fsio = new Fsio();
        $this->stdout = fopen('php://memory', 'w+');
        $this->logger = new Logger($this->stdout);
        $this->connection = (new DataSourceFixture())->exec();
        $this->config = new Config([
            'pdo' => [$this->connection->getPdo()],
            'directory' => __DIR__ . '/DataSource',
            'namespace' => 'Atlas\\Mapper\\DataSource',
        ]);
        $this->skeleton = new Skeleton($this->config, $this->fsio, $this->logger);
    }

    public function testInitialGeneration()
    {
        $ds = $this->config->directory;
        `rm -rf $ds/*`;

        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/Thread_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadEvents_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRecord_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRecordSet_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRelated_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadSelect_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadTable_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadTableEvents_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRow_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/_generated/ThreadTableSelect_.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/Thread.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadEvents.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadRecord.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadRecordSet.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadRelated.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadSelect.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadTable.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadTableEvents.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadRow.php"));
        $this->assertFalse($this->fsio->isFile("{$ds}/Thread/ThreadTableSelect.php"));

        ($this->skeleton)();

        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/Thread_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadEvents_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRecord_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRecordSet_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRelated_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadSelect_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadTable_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadTableEvents_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadRow_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/_generated/ThreadTableSelect_.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/Thread.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadEvents.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadRecord.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadRecordSet.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadRelated.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadSelect.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadTable.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadTableEvents.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadRow.php"));
        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadTableSelect.php"));
    }

    public function testRelatedFields()
    {
        $ds = $this->config->directory;
        $mapperDir = dirname(__DIR__) . '/vendor/atlas/mapper/tests/DataSource';
        $typeDirs = glob($mapperDir . '/[!.]*', GLOB_ONLYDIR);
        foreach ($typeDirs as $typeDir) {
            $type = strrchr($typeDir, DIRECTORY_SEPARATOR);
            copy(
                "{$typeDir}/{$type}Related.php",
                "{$ds}/{$type}/{$type}Related.php"
            );
        }

        // regenerate with relateds in place, to make sure nothing blows up
        ($this->skeleton)();

        $this->assertTrue($this->fsio->isFile("{$ds}/Thread/ThreadRelated.php"));
    }

    protected function readStdout()
    {
        $text = '';
        rewind($this->stdout);

        while ($read = fread($this->stdout, 8192)) {
            $text .= $read;
        }

        return $text;
    }
}
