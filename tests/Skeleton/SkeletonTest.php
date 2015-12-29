<?php
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\FakeFsio;
use Aura\Cli\CliFactory;

class SkeletonTest extends \PHPUnit_Framework_TestCase
{
    protected $skeleton;
    protected $stdout;
    protected $stderr;

    protected function setUp()
    {
        $this->fsio = $this->newFsio();

        $cliFactory = new CliFactory($GLOBALS);
        $this->stdio = $cliFactory->newStdio(
            'php://memory',
            'php://memory',
            'php://memory'
        );
        $this->stdout = $this->stdio->getStdout();
        $this->stderr = $this->stdio->getStderr();

        $this->skeleton = new Skeleton($this->fsio, $this->stdio);
    }

    protected function newFsio()
    {
        $fsio = new FakeFsio();

        // put the real templates into the fake fsio
        $dir = dirname(dirname(__DIR__)) . '/templates';
        $tpls = [
            'Mapper.tpl',
            'Plugin.tpl',
            'Record.tpl',
            'RecordSet.tpl',
            'Table.tpl',
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
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorPlugin.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorRecord.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorRecordSet.php'));
        $this->assertFalse($this->fsio->isFile('/app/DataSource/Author/AuthorTable.php'));

        $this->fsio->mkdir('/app/DataSource');

        $input = new SkeletonInput();
        $input->dir = '/app/DataSource';
        $input->namespace = 'App\\DataSource\\Author';
        $input->full = true;
        $input->conn = ['sqlite:' . dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fixture.sqlite'];
        $input->table = 'authors';

        $this->skeleton->__invoke($input);

        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorMapper.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorPlugin.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecord.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorRecordSet.php'));
        $this->assertTrue($this->fsio->isFile('/app/DataSource/Author/AuthorTable.php'));
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
