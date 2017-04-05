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

/**
 *
 * The Skeleton command input values.
 *
 * @package atlas/cli
 *
 */
class SkeletonInput
{
    /**
     *
     * Database connection information.
     *
     * @var array
     *
     */
    protected $conn;

    /**
     *
     * The base directory for writing files.
     *
     * @var string
     *
     */
    protected $dir;

    /**
     *
     * Write out the full set of skeleton files?
     *
     * @var bool
     *
     */
    protected $full = false;

    /**
     *
     * Use this as the namespace for the skeleton classes.
     *
     * @var string
     *
     */
    protected $namespace;

    /**
     *
     * Read columns from this table (if at all).
     *
     * @var string
     *
     */
    protected $table;

    /**
     *
     * A custom template directory.
     *
     * @var string
     *
     */
    protected $tpl;

    /**
     *
     * Magic set for properties.
     *
     * @param string $key The property name.
     *
     * @param string $val The property value.
     *
     * @return null
     *
     * @throws Exception if there is no such property.
     *
     */
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

    /**
     *
     * Magic get for properties.
     *
     * @param string $key The property name.
     *
     * @return mixed
     *
     * @throws Exception if there is no such property.
     *
     */
    public function __get($key)
    {
        if (! property_exists($this, $key)) {
            throw new Exception("No such property: $key");
        }

        return $this->$key;
    }
}
