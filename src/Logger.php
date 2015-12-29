<?php
namespace Atlas\Cli;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Aura\Cli\Stdio\Handle;

/**
 *
 * A basic logger implementation that writes to a resource handle.
 *
 */
class Logger implements LoggerInterface
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
     * System is unusable.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     *
     * Action must be taken immediately.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     *
     * Critical conditions.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     *
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     *
     * Exceptional occurrences that are not errors.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     *
     * Normal but significant events.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     *
     * Interesting events.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     *
     * Detailed debug information.
     *
     * @param string $message
     *
     * @param array $context
     *
     * @return null
     *
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
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
