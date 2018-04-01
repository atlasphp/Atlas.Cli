<?php
namespace Atlas\Cli;

use Atlas\Cli\FakeFsio;
use Atlas\Cli\Logger;
use Aura\Cli\Stdio\Handle;

class SkeletonTest extends \PHPUnit\Framework\TestCase
{
    protected $fsio;
    protected $logger;
    protected $stdout;
    protected $factory;

    protected function setUp()
    {
        $this->fsio = $this->newFsio();
        $this->stdout = fopen('php://memory', 'w+');
        $this->logger = new Logger($this->stdout);
    }

    protected function newFsio()
    {
        $fsio = new FakeFsio();

        // put the real templates into the fake fsio
        $dir = dirname(__DIR__) . '/resources/templates';
        $tpls = [
            'Fields.tpl',
            'Mapper.tpl',
            'MapperEvents.tpl',
            'Record.tpl',
            'RecordSet.tpl',
            'Row.tpl',
            'Table.tpl',
            'TableEvents.tpl',
        ];
        foreach ($tpls as $tpl) {
            $file = $dir . DIRECTORY_SEPARATOR . $tpl;
            $fsio->put($file, file_get_contents($file));
        }

        return $fsio;
    }

    public function test()
    {
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorFields.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorMapper.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorMapperEvents.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorRecord.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorRecordSet.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorRow.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorTable.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorTableEvents.php'));

        $this->fsio->mkdir('/app/DataSource');

        $config = new Config([
            'pdo' => 'sqlite:' . __DIR__ . '/fixture.sqlite',
            'directory' => '/app/DataSource',
            'namespace' => 'App\\DataSource\\Author',
        ]);

        $skeleton = new Skeleton($config, $this->fsio, $this->logger);
        $skeleton();

        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorFields.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorMapper.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorMapperEvents.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecord.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecordSet.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRow.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorTable.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorTableEvents.php'));
    }

    protected function readHandle($handle)
    {
        $text = '';
        rewind($handle);
        while ($read .= fread($handle, 8192)) {
            $text .= $read;
        }
        return $text;
    }
}
