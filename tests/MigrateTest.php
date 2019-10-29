<?php
namespace Atlas\Cli;

use PDO;
use Atlas\Cli\Migration\V004;

class MigrateTest extends \PHPUnit\Framework\TestCase
{
    protected $pdo;

    protected $output = array();

    protected $migrate;

    public function setUp()
    {
        V004::$throw = false;

        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('CREATE TABLE migration (version INT)');
        $pdo->exec('INSERT INTO migration (version) VALUES (0)');

        $config = new Config([
            'pdo' => [$pdo],
            'migration' => [
                'directory' => __DIR__ . DIRECTORY_SEPARATOR . 'migrations',
                'namespace' => 'Atlas\Cli\Migration',
                'table' => 'migration',
                'column' => 'version',
            ],
        ]);

        $this->fsio = new Fsio();
        $this->stdout = fopen('php://memory', 'w+');
        $this->logger = new Logger($this->stdout);
        $this->migrate = new Migrate($config, $this->fsio, $this->logger);
    }

    public function assertStdout(array $messages)
    {
        $actual = '';

        rewind($this->stdout);
        while (! feof($this->stdout)) {
            $actual .= fread($this->stdout, 8192);
        }

        $expect = implode(PHP_EOL, $messages);
        $this->assertStringContainsString(trim($expect), trim($actual));
    }

    public function testUpAndDown()
    {
        $this->migrate->up();
        $this->migrate->down();
        $expect = [
            'Migrating up from 0 to V004.',
            'Migration up to V001 committed!',
            'Migration up to V002 committed!',
            'Migration up to V003 committed!',
            'Migration up to V004 committed!',
            'Now at V004.',
            'Migrating down from V004 to V001.',
            'Migration down from V004 committed!',
            'Migration down from V003 committed!',
            'Migration down from V002 committed!',
            'Now at V001.',
        ];

        $this->assertStdout($expect);
    }

    public function testUpAndDownTo()
    {
        $this->migrate->up('V002');
        $this->migrate->down('V001');
        $expect = [
            'Migrating up from 0 to V002.',
            'Migration up to V001 committed!',
            'Migration up to V002 committed!',
            'Now at V002.',
            'Migrating down from V002 to V001.',
            'Migration down from V002 committed!',
            'Now at V001.',
        ];

        $this->assertStdout($expect);
    }

    public function testUpWhenAlreadyPast()
    {
        $this->migrate->up('V002');
        $this->migrate->up('V001');
        $expect = [
            'Migrating up from 0 to V002.',
            'Migration up to V001 committed!',
            'Migration up to V002 committed!',
            'Now at V002.',
            'Current version V002 is already at or later than target version V001.',
        ];
        $this->assertStdout($expect);
    }

    public function testDownWhenAlreadyPast()
    {
        $this->migrate->up('V002');
        $this->migrate->down('V003');
        $expect = [
            'Migrating up from 0 to V002.',
            'Migration up to V001 committed!',
            'Migration up to V002 committed!',
            'Now at V002.',
            'Current version V002 is already at or earlier than target version V003.',
        ];
        $this->assertStdout($expect);
    }

    public function testNoSuchVersion()
    {
        $this->migrate->up('V005');
        $this->migrate->down('V005');
        $expect = [
            'Version V005 not recognized.',
            'Version V005 not recognized.',
        ];
        $this->assertStdout($expect);
    }

    public function testUpException()
    {
        V004::$throw = true;
        $this->migrate->up('V004');
        $expect = [
            'Migrating up from 0 to V004.',
            'Migration up to V001 committed!',
            'Migration up to V002 committed!',
            'Migration up to V003 committed!',
            'Migration up to V004 failed.',
            'Atlas\Cli\Exception: fake up error',
        ];
        $this->assertStdout($expect);
    }

    public function testDownException()
    {
        $this->migrate->up('V004');
        V004::$throw = true;
        $this->migrate->down();
        $expect = [
            'Migrating up from 0 to V004.',
            'Migration up to V001 committed!',
            'Migration up to V002 committed!',
            'Migration up to V003 committed!',
            'Migration up to V004 committed!',
            'Now at V004.',
            'Migrating down from V004 to V001.',
            'Migration down from V004 failed.',
            'Atlas\Cli\Exception: fake down error',
        ];
        $this->assertStdout($expect);
    }
}
