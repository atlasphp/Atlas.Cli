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
    public array $pdo;
    public string $directory;
    public string $namespace;
    public Transform $transform;
    public string $templates;

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

    public function __get(string $key) : mixed
    {
        throw new Exception("No such property: $key");
    }
}
