<?php
namespace Atlas\Cli;

use Atlas\Pdo\Connection;

abstract class Migration
{
    abstract public function up(Connection $connection) : void;
    abstract public function down(Connection $connection) : void;
}
