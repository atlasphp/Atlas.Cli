#!/usr/bin/env php
<?php
use Aura\Cli\CliFactory;
use Atlas\Cli\Skeleton\SkeletonCommand;
use Atlas\Cli\Skeleton\SkeletonInput;
use Atlas\Cli\Fsio;

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
$command = new SkeletonCommand(
    $cliFactory->newContext($GLOBALS),
    $cliFactory->newStdio(),
    new Fsio(),
    new SkeletonInput()
);
$code = $command();
exit($code);
