<?php
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\FakeFsio;
use Aura\Cli\CliFactory;

class SkeletonTest extends \PHPUnit_Framework_TestCase
{
    protected $skeleton;

    protected function setUp()
    {
        $this->fsio = new FakeFsio();

        $cliFactory = new CliFactory($GLOBALS);
        $this->stdio = $cliFactory->newStdio(
            'php://memory',
            'php://memory',
            'php://memory'
        );

        $this->skeleton = new Skeleton($this->fsio, $this->stdio);
    }

    public function test()
    {
        $this->assertInstanceOf(Skeleton::CLASS, $this->skeleton);
    }
}
