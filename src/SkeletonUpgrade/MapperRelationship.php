<?php
/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 */
declare(strict_types=1);

namespace Atlas\Cli\SkeletonUpgrade;

class MapperRelationship
{
    protected array $calls = [];

    protected array $imports = [];

    public function __construct(
        public string $relatedName,
        public string $defineClass,
        public string $relatedType,
        public ?array $on = [],
        public ?string $extraName = null,
        public ?string $extraValue = null,
    ) {
        if ($relatedType !== 'mixed') {
            $this->imports[] = $relatedType;
        }
    }

    public function imports()
    {
    	return $this->imports;
    }

    public function property()
    {
        $on = '';
        if (! empty($this->on)) {
            $on = $this->fixOn($this->on);
        }

        $extra = '';
        if ($this->extraName !== null) {
            $extra = $this->extraName . ': ' . var_export($this->extraValue, true);
        }

        $define = "#[{$this->defineClass}";
        if ($on || $extra) {
            $comma = $on && $extra ? ', ' : '';
            $define .= '(';
            $define .= $on . $comma . $extra;
            $define = rtrim($define, ', ');
            $define .= ')';
        }

        $define .= ']' . PHP_EOL;

        $called = '';
        foreach ($this->calls as $call) {
            list ($attr, $args) = $call;
            $called .= "    #[{$attr}";
            if (! empty($args)) {
                $called .= '(' . implode(', ', $args) . ')';
            }
            $called .= ']' . PHP_EOL;
        }

        $short = $this->shortClass($this->relatedType);
        if (substr($short, -6) === 'Record') {
            $short = "?{$short}";
        }

        $property = "    protected {$short} \${$this->relatedName};". PHP_EOL;
        return $define . $called . $property;
    }

    protected function fixOn(array $on)
    {
        if (empty($on)) {
            return '';
        }

        $on = var_export($on, true);
        $on = str_replace('array (' . PHP_EOL, '', $on);
        $on = str_replace(PHP_EOL . ')', '', $on);
        $on = str_replace('  ', '', $on);
        $on = str_replace(PHP_EOL, ' ', $on);
        $on = rtrim($on, ', '. PHP_EOL);
        return 'on: [' . $on . ']';
    }

    protected function shortClass(string $fqcn)
    {
        $parts = explode('\\', $fqcn);
        return end($parts);
    }

    public function type(
        string $typeVal,
        string $foreignMapperClass,
        array $on
    ) : self
    {
        $variantType = $foreignMapperClass . 'Record';
        $this->imports[] = $variantType;
        $short = $this->shortClass($variantType);
        $args = [
            var_export($typeVal, true),
            $short . '::CLASS',
            $this->fixOn($on),
        ];
        $this->calls[] = ['Define\\Variant', $args];
        return $this;
    }

    public function ignoreCase(bool $ignoreCase = null) : self
    {
        $args = ($ignoreCase === null) ? [] : [var_export($ignoreCase, true)];
        $this->calls[] = ['Define\\IgnoreCase', $args];
        return $this;
    }

    public function where(string $condition, ...$bindInline) : self
    {
        $args = [];
        foreach (func_get_args() as $arg) {
            $args[] = var_export($arg, true);
        }
        $this->calls[] = ['Define\\Where', $args];
        return $this;
    }

    public function onDeleteCascade() : self
    {
        $this->calls[] = ['Define\\OnDelete\\Cascade', []];
        return $this;
    }

    public function onDeleteInitDeleted() : self
    {
        $this->calls[] = ['Define\\OnDelete\\InitDeleted', []];
        return $this;
    }

    public function onDeleteSetDelete() : self
    {
        $this->calls[] = ['Define\\OnDelete\\SetDelete', []];
        return $this;
    }

    public function onDeleteSetNull() : self
    {
        $this->calls[] = ['Define\\OnDelete\\SetNull', []];
        return $this;
    }
}
