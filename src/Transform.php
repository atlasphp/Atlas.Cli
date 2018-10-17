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

class Transform
{
    protected $types = [];

    protected $skip = [
        'sqlite_sequence',
    ];

    protected $unchanged = [
        'equipment',
        'fish',
        'information',
        'money',
        'rice',
        'series',
        'sheep',
        'sms',
        'species',
        'status',
    ];

    protected $irregular = [
        'child' => 'children',
        'man' => 'men',
        'move' => 'moves',
        'person' => 'people',
        'sex' => 'sexes',
    ];

    protected $singular = [
        '/(quiz)zes$/i' => '$1',
        '/(matr)ices$/i' => '$1ix',
        '/(vert|ind)ices$/i' => '$1ex',
        '/^(ox)en/i' => '$1',
        '/(alias|status)es$/i' => '$1',
        '/([octop|vir])i$/i' => '$1us',
        '/(cris|ax|test)es$/i' => '$1is',
        '/(shoe)s$/i' => '$1',
        '/(o)es$/i' => '$1',
        '/(bus)es$/i' => '$1',
        '/([m|l])ice$/i' => '$1ouse',
        '/(x|ch|ss|sh)es$/i' => '$1',
        '/(m)ovies$/i' => '$1ovie',
        '/(s)eries$/i' => '$1eries',
        '/([^aeiouy]|qu)ies$/i' => '$1y',
        '/([lr])ves$/i' => '$1f',
        '/(tive)s$/i' => '$1',
        '/(hive)s$/i' => '$1',
        '/([^f])ves$/i' => '$1fe',
        '/(^analy)ses$/i' => '$1sis',
        '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '$1$2sis',
        '/([ti])a$/i' => '$1um',
        '/(n)ews$/i' => '$1ews',
        '/s[1]$/i' => ''
    ];

    /**
     * @param array $types An array of key-value pairs where the key
     * is a table name and the value is the type name to return for it.
     */
    public function __construct(array $types = [])
    {
        $this->types = $types;
    }

    public function __invoke(string $table) : ?string
    {
        if (array_key_exists($table, $this->types)) {
            return $this->types[$table];
        }

        if (in_array($table, $this->skip)) {
            return null;
        }

        $table = str_replace('_', '', ucwords(ucfirst($table), '_'));

        $lower = strtolower($table);
        foreach ($this->unchanged as $unchanged) {
            if (substr($lower, (-1 * strlen($unchanged))) == $unchanged) {
                return $table;
            }
        }

        foreach ($this->irregular as $singular => $plural) {
            if (preg_match('/('. $plural . ')$/i', $table, $matches)) {
                return preg_replace(
                    '/(' . $plural . ')$/i',
                    substr($matches[0], 0, 1) . substr($singular, 1),
                    $table
                );
            }
        }

        foreach ($this->singular as $rule => $replacement) {
            if (preg_match($rule, $table)) {
                return preg_replace($rule, $replacement, $table);
            }
        }

        return $table;
    }
}
