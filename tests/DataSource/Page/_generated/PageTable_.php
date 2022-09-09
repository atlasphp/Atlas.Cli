<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Mapper\DataSource\Page\_generated;

use Atlas\Table\Table;
use Atlas\Mapper\DataSource\Page\PageRow;
use Atlas\Mapper\DataSource\Page\PageTableSelect;

/**
 * @method ?PageRow fetchRow(mixed $primaryVal)
 * @method PageRow[] fetchRows(array $primaryVals)
 * @method PageTableSelect select(array $whereEquals = [])
 * @method PageRow newRow(array $cols = [])
 * @method PageRow newSelectedRow(array $cols)
 */
abstract class PageTable_ extends Table
{
    public const DRIVER = 'sqlite';

    public const NAME = 'pages';

    public const COLUMNS = [
        'page_id' => [
            'name' => 'page_id',
            'type' => 'INTEGER',
            'size' => null,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => true,
            'primary' => true,
            'options' => null,
        ],
        'title' => [
            'name' => 'title',
            'type' => 'VARCHAR',
            'size' => 255,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'body' => [
            'name' => 'body',
            'type' => 'TEXT',
            'size' => null,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
    ];

    public const PRIMARY_KEY = [
        'page_id',    ];

    public const COMPOSITE_KEY = false;

    public const AUTOINC_COLUMN = 'page_id';

    public const AUTOINC_SEQUENCE = null;

    public const ROW_CLASS = PageRow;
}