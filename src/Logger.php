<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Cli;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    protected $handle;

    /**
     * @param resource $handle A resource suitable for fwrite().
     */
    public function __construct($handle = STDOUT)
    {
        $this->handle = $handle;
    }

    public function log($level, $message, array $context = []) : void
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $message = strtr($message, $replace);
        fwrite($this->handle, $message . PHP_EOL);
    }
}
