<?php
namespace Atlas\Cli;

class FakeFsio extends Fsio
{
    protected $files = array();
    protected $dirs = array();

    public function get(string $file) : string
    {
        return $this->files[$file];
    }

    public function put(string $file, string $data) : int
    {
        $this->files[$file] = $data;
        return strlen($data);
    }

    public function isFile(string $file) : bool
    {
        return isset($this->files[$file]);
    }

    public function isDir(string $dir) : bool
    {
        return isset($this->dirs[$dir]);
    }

    public function mkdir(string $dir, int $mode = 0777, bool $deep = true) : void
    {
        $this->dirs[$dir] = true;
    }
}
