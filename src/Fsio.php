<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Cli;

use Atlas\Cli\Exception;

/**
 *
 * File system input/output.
 *
 * @package atlas/cli
 *
 */
class Fsio
{
    /**
     *
     * Equivalent of file_get_contents().
     *
     * @param string $file Read from this file.
     *
     * @return string The file contents.
     *
     * @throws Exception on error.
     *
     */
    public function get($file)
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

    /**
     *
     * Equivalent of file_put_contents().
     *
     * @param string $file Write to this file.
     *
     * @param string $data The data to write.
     *
     * @return int The number of bytes written.
     *
     * @throws Exception on error.
     *
     */
    public function put($file, $data)
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

    /**
     *
     * Does a file exist, and is it readable?
     *
     * @param string $file The file to check.
     *
     * @return bool
     *
     */
    public function isFile($file)
    {
        return file_exists($file) && is_readable($file);
    }

    /**
     *
     * Does a directory exist?
     *
     * @param string $dir The directory to check.
     *
     * @return bool
     *
     */
    public function isDir($dir)
    {
        return is_dir($dir);
    }

    /**
     *
     * Equivalent of mkdir().
     *
     * @param string $dir Create this directory.
     *
     * @param mixed $mode The permsissions mode.
     *
     * @param bool $deep Create intervening directories?
     *
     * @return null
     *
     * @throws Exception on error.
     *
     */
    public function mkdir($dir, $mode = 0777, $deep = true)
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

    /**
     *
     * Equivalent of getcwd().
     *
     * @return string
     *
     */
    public function getCwd()
    {
        return getcwd();
    }
}
