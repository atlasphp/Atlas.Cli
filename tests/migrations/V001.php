<?php
namespace Atlas\Cli\Migration;

use Atlas\Cli\Migration;
use Atlas\Pdo\Connection;

class V001 extends Migration
{
    public function up(Connection $connection) : void
    {
        $connection->perform("CREATE TABLE v1table (name VARCHAR(50))");
    }

    public function down(Connection $connection) : void
    {
        $connection->perform("DROP TABLE v1table");
    }
}
