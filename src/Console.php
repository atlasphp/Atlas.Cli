<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Cli;

use Throwable;

class Console
{
    public static function run(string $class, Config|array $config) : int
    {
        try {
            if (is_array($config)) {
                $config = static::newConfig($config);
            }

            $fsio = new Fsio();
            $logger = new Logger();
            $command = new $class($config, $fsio, $logger);
            $code = $command();
        } catch (Throwable $e) {
            echo $e->getMessage() . PHP_EOL;
            $code = 1;
        }

        return (int) $code;
    }

    protected static function newConfig(array $argv) : Config
    {
        if (! isset($argv[1])) {
            throw new Exception("Please specify the path to a config file.");
        }

        $configFile = $argv[1];

        if (! file_exists($configFile) && ! is_readable($configFile)) {
            throw new Exception("Config file missing or not readable: {$configFile}");
        }

        $input = require $configFile;

        if (! is_array($input)) {
            throw new Exception("Config file '$configFile' does not return a PHP array.");
        }

        $keys = isset($argv[2])
            ? explode('.', $argv[2])
            : [];

        foreach ($keys as $key) {
            if (! isset($input[$key]) || ! is_array($input[$key])) {
                throw new Exception(
                    "Nested config key '{$key}' is not set, or is not an array."
                );
            }

            $input = $input[$key];
        }

        return new Config($input);
    }
}
