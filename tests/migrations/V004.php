<?php
namespace Atlas\Cli\Migration;

use Atlas\Cli\Exception;
use Atlas\Cli\Migration;
use Atlas\Pdo\Connection;

class V004 extends Migration
{
    public static $throw = false;

    public function up(Connection $connection) : void
    {
        $connection->perform("CREATE TABLE v4table (name VARCHAR(50))");

        if (self::$throw) {
            throw new Exception('fake up error');
        }
    }

    public function down(Connection $connection) : void
    {
        $connection->perform("DROP TABLE v4table");

        if (self::$throw) {
            throw new Exception('fake down error');
        }
    }
}
