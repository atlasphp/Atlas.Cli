#!/usr/bin/env php
<?php
use Atlas\Cli\Fsio;
use Atlas\Cli\Logger;
use Atlas\Cli\Skeleton\SkeletonCommand;
use Atlas\Cli\Skeleton\SkeletonFactory;
use Aura\Cli\CliFactory;

error_reporting(E_ALL);

$autoload = false;

$files = array(
    __DIR__ . '/../../../autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php'
);

foreach ($files as $file) {
    if (file_exists($file)) {
        $autoload = $file;
        break;
    }
}

if (! $autoload) {
    echo "Please install and update Composer before continuing." . PHP_EOL;
    exit(1);
}

require $autoload;

$cliFactory = new CliFactory();
$context = $cliFactory->newContext($GLOBALS);
$stdio = $cliFactory->newStdio();
$fsio = new Fsio();
$logger = new Logger($stdio->getStdout());
$skeletonFactory = new SkeletonFactory($fsio, $logger);
$command = new SkeletonCommand(
    $context,
    $stdio,
    $fsio,
    $skeletonFactory
);

$code = $command();
exit($code);
