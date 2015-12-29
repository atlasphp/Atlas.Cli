<?php
namespace Atlas\Cli\Skeleton;

class SkeletonInput
{
    protected $conn;
    protected $dir;
    protected $full = false;
    protected $namespace;
    protected $table;

    public function __set($key, $val)
    {
        if (! property_exists($this, $key)) {
            throw new Exception();
        }

        $this->$key = $val;
    }

    public function __get($key)
    {
        if (! property_exists($this, $key)) {
            throw new Exception();
        }

        return $this->$key;
    }
}
