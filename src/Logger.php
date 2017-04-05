<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
namespace Atlas\Cli;

use Aura\Cli\Stdio\Handle;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 *
 * A basic logger implementation that writes to a resource handle.
 *
 * @package atlas/cli
 *
 */
class Logger extends AbstractLogger
{
    /**
     *
     * The resource handle.
     *
     * @var Handle
     *
     */
    protected $handle;

    /**
     *
     * Constructor.
     *
     * @param Handle $handle The resource handle to write to.
     *
     */
    public function __construct(Handle $handle)
    {
        $this->handle = $handle;
    }

    /**
     *
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function log($level, $message, array $context = [])
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $message = strtr($message, $replace);
        $this->handle->fwrite($message . PHP_EOL);
    }
}
