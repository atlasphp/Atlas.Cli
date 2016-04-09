<?php
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\FakeFsio;
use Atlas\Cli\Logger;
use Aura\Cli\Stdio\Handle;

class SkeletonTest extends \PHPUnit_Framework_TestCase
{
    protected $fsio;
    protected $logger;
    protected $stdout;
    protected $factory;

    protected function setUp()
    {
        $this->fsio = $this->newFsio();
        $this->stdout = new Handle('php://memory', 'w+');
        $this->logger = new Logger($this->stdout);
        $this->factory = new SkeletonFactory($this->fsio, $this->logger);
    }

    protected function newFsio()
    {
        $fsio = new FakeFsio();

        // put the real templates into the fake fsio
        $dir = dirname(dirname(__DIR__)) . '/templates';
        $tpls = [
            'Mapper.tpl',
            'MapperEvents.tpl',
            'Record.tpl',
            'RecordSet.tpl',
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
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorMapper.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorMapperEvents.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorRecord.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorRecordSet.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorTable.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorTableEvents.php'));

        $this->fsio->mkdir('/app/DataSource');

        $input = $this->factory->newSkeletonInput();
        $input->dir = '/app/DataSource';
        $input->namespace = 'App\\DataSource\\Author';
        $input->full = true;
        $input->conn = ['sqlite:' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixture.sqlite'];
        $input->table = 'authors';

        $skeleton = $this->factory->newSkeleton();
        $skeleton($input);

        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorMapper.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorMapperEvents.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecord.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecordSet.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorTable.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorTableEvents.php'));
    }

    public function testFactoryWithConnection()
    {
        $conn = ['sqlite:' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixture.sqlite'];
        $input = $this->factory->newSkeletonInput();
        $input->dir = '/app/DataSource';
        $input->namespace = 'App\\DataSource\\Author';
        $input->full = true;
        $input->table = 'authors';
        $factory = new SkeletonFactory($this->fsio, $this->logger, $conn);

        $skeleton = $factory->newSkeleton();
        $skeleton($input);

        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorMapper.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorMapperEvents.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecord.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecordSet.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorTable.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorTableEvents.php'));
    }

    protected function readHandle($handle)
    {
        $text = '';
        $handle->rewind();
        while ($read .= $handle->fread(8192)) {
            $text .= $read;
        }
        return $text;
    }
}
