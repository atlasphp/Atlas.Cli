<?php
namespace Atlas\Cli;

class FakeFsio extends Fsio
{
    protected $files = array();
    protected $dirs = array();
    protected $timestamps = array();

    public function get($file)
    {
        return $this->files[$file];
    }

    public function put($file, $data)
    {
        $this->files[$file] = $data;
        $this->timestamps[$file] = time();
    }

    public function isFile($file)
    {
        return isset($this->files[$file]);
    }

    public function isDir($dir)
    {
        return isset($this->dirs[$dir]);
    }

    public function mkdir($dir, $mode = 0777, $deep = true)
    {
        $this->dirs[$dir] = true;
    }

    public function getModifiedTime($file)
    {
        return $this->timestamps[$file] ?: 0;
    }
}
