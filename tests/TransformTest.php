<?php
namespace Atlas\Cli;

class TransformTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $transform = new Transform();
        foreach ($this->tables as $table => $expect) {
            $actual = $transform($table);
            $this->assertSame($expect, $actual);
        }
    }

    protected $tables = [
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
