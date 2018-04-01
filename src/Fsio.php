<?php
declare(strict_types=1);

/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Cli;

use Atlas\Cli\Exception;

class Fsio
{
    public function get(string $file) : string
    {
        $level = error_reporting(0);
        $result = file_get_contents($file);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    public function put(string $file, string $data) : int
    {
        $level = error_reporting(0);
        $result = file_put_contents($file, $data);
        error_reporting($level);

        if ($result !== false) {
            return $result;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    public function isFile(string $file) : bool
    {
        return file_exists($file) && is_readable($file);
    }

    public function isDir(string $dir) : bool
    {
        return is_dir($dir);
    }

    public function mkdir(string $dir, int $mode = 0777, bool $deep = true) : void
    {
        $level = error_reporting(0);
        $result = mkdir($dir, $mode, $deep);
        error_reporting($level);

        if ($result !== false) {
            return;
        }

        $error = error_get_last();
        throw new Exception($error['message']);
    }

    public function getCwd() : string
    {
        return getcwd();
    }
}
