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

class MapperRelationships
{
    protected $rels = [];

    public function properties()
    {
        $str = '';
        foreach ($this->rels as $rel) {
            $str .= '    ' . $rel->property() . PHP_EOL;
        }
        return rtrim($str);
    }

    public function imports()
    {
        $str = [];

        foreach ($this->rels as $rel) {
            foreach ($rel->imports() as $import) {
                $str[] = 'use ' . $import . ';';
            }
        }

        $str = array_unique($str);
        return implode(PHP_EOL, $str);
    }

    protected function oneToOne(
        string $relatedName,
        string $foreignMapperClass,
        array $on = []
    ) : MapperRelationship
    {
        return $this->add(
            $relatedName,
            'Define\\OneToOne',
            $foreignMapperClass,
            $on
        );
    }

    protected function oneToOneBidi(
        string $relatedName,
        string $foreignMapperClass,
        array $on = []
    ) : MapperRelationship
    {
        return $this->add(
            $relatedName,
            'Define\\OneToOneBidi',
            $foreignMapperClass,
            $on
        );
    }

    protected function oneToMany(
        string $relatedName,
        string $foreignMapperClass,
        array $on = []
    ) : MapperRelationship
    {
        return $this->add(
            $relatedName,
            'Define\\OneToMany',
            $foreignMapperClass,
            $on
        );
    }

    protected function manyToOne(
        string $relatedName,
        string $foreignMapperClass,
        array $on = []
    ) : MapperRelationship
    {
        return $this->add(
            $relatedName,
            'Define\\ManyToOne',
            $foreignMapperClass,
            $on
        );
    }

    protected function manyToOneVariant(
        string $relatedName,
        string $referenceCol
    ) : MapperRelationship
    {
        return $this->add(
            $relatedName,
            'Define\\ManyToOneVariant',
            'mixed',
            [],
            'column',
            $referenceCol,
        );
    }

    protected function manyToMany(
        string $relatedName,
        string $foreignMapperClass,
        string $throughRelatedName = null,
        array $on = []
    ) : MapperRelationship
    {
        return $this->add(
            $relatedName,
            'Define\\ManyToMany',
            $foreignMapperClass,
            $on,
            'through',
            $throughRelatedName
        );
    }

    protected function add(
        string $relatedName,
        string $defineClass,
        string $foreignMapperClass,
        ?array $on = [],
        ?string $extraName = null,
        ?string $extraValue = null,
    ) : MapperRelationship
    {
        switch (true) {
            case strpos($defineClass, 'ToOneVariant') !== false:
                $relatedType = 'mixed';
                break;

            case strpos($defineClass, 'ToOne') !== false:
                $relatedType = $foreignMapperClass . 'Record';
                break;

            default:
                $relatedType = $foreignMapperClass . 'RecordSet';
                break;
        }

        $rel = new MapperRelationship(
            $relatedName,
            $defineClass,
            $relatedType,
            $on,
            $extraName,
            $extraValue,
        );

        $this->rels[] = $rel;
        return $rel;
    }
}
