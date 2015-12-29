<?php
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\Fsio;
use Atlas\Cli\Logger;

class SkeletonFactory
{
    protected $fsio;
    protected $logger;

    public function __construct(Fsio $fsio, Logger $logger)
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
    }

    public function newSkeleton()
    {
        return new Skeleton($this->fsio, $this->logger);
    }

    public function newSkeletonInput()
    {
        return new SkeletonInput();
    }
}
