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
use Stringable;

class Logger extends AbstractLogger
{
    /**
     * @param resource $handle
     */
    public function __construct(protected mixed $handle = STDOUT)
    {
    }

    public function log(
        mixed $level,
        string|Stringable $message,
        array $context = []
    ) : void
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        $message = strtr((string) $message, $replace);
        fwrite($this->handle, $message . PHP_EOL);
    }
}
