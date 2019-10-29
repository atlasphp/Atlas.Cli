<?php
namespace Atlas\Cli\Migration;

use Atlas\Cli\Migration;
use Atlas\Pdo\Connection;

class V002 extends Migration
{
    public function up(Connection $connection) : void
    {
        $connection->perform("ALTER TABLE v1table RENAME TO v2table");
    }

    public function down(Connection $connection) : void
    {
        $connection->perform("ALTER TABLE v2table RENAME TO v1table");
    }
}
