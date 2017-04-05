<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\Exception;

class SkeletonInput
{
    protected $conn;
    protected $dir;
    protected $full = false;
    protected $namespace;
    protected $table;
    protected $tpl;

    public function __set($key, $val)
    {
        switch ($key) {
            case 'conn':
                $val = (array) $val;
                break;
            case 'dir':
                $val = rtrim(trim($val), DIRECTORY_SEPARATOR);
                break;
            case 'full':
                $val = (bool) $val;
                break;
            case 'namespace':
                $val = rtrim(trim($val), '\\');
                break;
            case 'table':
                $val = trim($val);
                break;
            case 'tpl':
                $val = rtrim(trim($val), DIRECTORY_SEPARATOR);
                break;
            default:
                throw new Exception("No such property: $key");
        }

        $this->$key = $val;
    }

    public function __get($key)
    {
        if (! property_exists($this, $key)) {
            throw new Exception("No such property: $key");
        }

        return $this->$key;
    }
}
