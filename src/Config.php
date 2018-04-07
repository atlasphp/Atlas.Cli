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

use Atlas\Cli\Exception;

class Config
{
    protected $pdo;
    protected $directory;
    protected $namespace;
    protected $transform;
    protected $templates;

    public function __construct(array $input)
    {
        if (! isset($input['pdo'])) {
            throw new Exception("Config key 'pdo' is missing.");
        }

        $this->pdo = (array) $input['pdo'];

        $this->directory = $input['directory'] ?? '.';
        $this->directory = rtrim($this->directory, DIRECTORY_SEPARATOR);

        $this->namespace = $input['namespace'] ?? '';
        $this->namespace = rtrim($this->namespace, '\\');

        $this->transform = $input['transform'] ?? new Transform();

        $this->templates = $input['templates'] ?? dirname(__DIR__) . '/resources/templates';
        $this->templates = rtrim($this->templates, DIRECTORY_SEPARATOR);
    }

    public function __get(string $key)
    {
        if (! property_exists($this, $key)) {
            throw new Exception("No such property: $key");
        }

        return $this->$key;
    }
}
