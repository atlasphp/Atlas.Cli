<?php
namespace Atlas\Cli;

class TransformTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $transform = new Transform();
        foreach ($this->words as $original => $expect) {
            $actual = $transform($original);
            $this->assertSame($expect, $actual);
        }
    }

    protected $words = [
        'addresses' => 'Address',
        'classes' => 'Class',
        'illnesses' => 'Illness',
        'passes' => 'Pass',
        'presses' => 'Press',
        'address' => 'Address',
        'class' => 'Class',
        'illness' => 'Illness',
        'pass' => 'Pass',
        'press' => 'Press',
    ];
}
