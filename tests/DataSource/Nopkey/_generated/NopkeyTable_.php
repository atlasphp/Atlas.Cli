<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Mapper\DataSource\Nopkey\_generated;

use Atlas\Table\Table;
use Atlas\Mapper\DataSource\Nopkey\NopkeyRow;
use Atlas\Mapper\DataSource\Nopkey\NopkeyTableSelect;

/**
 * @method ?NopkeyRow fetchRow(mixed $primaryVal)
 * @method NopkeyRow[] fetchRows(array $primaryVals)
 * @method NopkeyTableSelect select(array $whereEquals = [])
 * @method NopkeyRow newRow(array $cols = [])
 * @method NopkeyRow newSelectedRow(array $cols)
 */
abstract class NopkeyTable_ extends Table
{
    public const DRIVER = 'sqlite';

    public const NAME = 'nopkeys';

    public const COLUMNS = [
        'name' => [
            'name' => 'name',
            'type' => 'VARCHAR',
            'size' => 255,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'email' => [
            'name' => 'email',
            'type' => 'VARCHAR',
            'size' => 255,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
    ];

    public const PRIMARY_KEY = [    ];

    public const COMPOSITE_KEY = false;

    public const AUTOINC_COLUMN = null;

    public const AUTOINC_SEQUENCE = null;

    public const ROW_CLASS = NopkeyRow;
}