<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Cli\Skeleton;

use Atlas\Cli\Fsio;
use Atlas\Cli\Logger;

/**
 *
 * Creates the Skeleton object.
 *
 * @package atlas/cli
 *
 */
class SkeletonFactory
{
    /**
     *
     * Filesystem I/O handler.
     *
     * @var Fsio
     *
     */
    protected $fsio;

    /**
     *
     * Logger for output.
     *
     * @var Logger
     *
     */
    protected $logger;

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
     * Constructor.
     *
     * @param Fsio $fsio A filesystem I/O handler.
     *
     * @param Logger $logger The output logger.
     *
     * @param array $conn Database connection information.
     *
     */
    public function __construct(Fsio $fsio, Logger $logger, array $conn = [])
    {
        $this->fsio = $fsio;
        $this->logger = $logger;
        $this->conn = $conn;
    }

    /**
     *
     * Returns a new Skeleton object.
     *
     * @return Skeleton
     *
     */
    public function newSkeleton()
    {
        return new Skeleton($this->fsio, $this->logger, $this->conn);
    }

    /**
     *
     * Returns a new SkeletonInput object.
     *
     * @return SkeletonInput
     *
     */
    public function newSkeletonInput()
    {
        return new SkeletonInput();
    }
}
