<?php
namespace Atlas\Cli;

class FakeFsio extends Fsio
{
    protected $files = array();
    protected $dirs = array();
    protected $saveCount = array();

    public function get($file)
    {
        return $this->files[$file];
    }

    public function put($file, $data)
    {
        $this->files[$file] = $data;
        if (!isset($this->saveCount[$file])) {
            $this->saveCount[$file] = 0;
        }
        $this->saveCount[$file]++;
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

    public function getSaveCount($file)
    {
        return $this->saveCount[$file] ?: 0;
    }
}
