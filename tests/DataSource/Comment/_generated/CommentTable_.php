<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Mapper\DataSource\Comment\_generated;

use Atlas\Table\Table;
use Atlas\Mapper\DataSource\Comment\CommentRow;
use Atlas\Mapper\DataSource\Comment\CommentTableSelect;

/**
 * @method ?CommentRow fetchRow(mixed $primaryVal)
 * @method CommentRow[] fetchRows(array $primaryVals)
 * @method CommentTableSelect select(array $whereEquals = [])
 * @method CommentRow newRow(array $cols = [])
 * @method CommentRow newSelectedRow(array $cols)
 */
abstract class CommentTable_ extends Table
{
    public const DRIVER = 'sqlite';

    public const NAME = 'comments';

    public const COLUMNS = [
        'comment_id' => [
            'name' => 'comment_id',
            'type' => 'INTEGER',
            'size' => null,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => true,
            'primary' => true,
            'options' => null,
        ],
        'related_type' => [
            'name' => 'related_type',
            'type' => 'VARCHAR',
            'size' => 255,
            'scale' => null,
            'notnull' => false,
            'default' => null,
            'autoinc' => false,
            'primary' => false,
            'options' => null,
        ],
        'related_id' => [
            'name' => 'related_id',
            'type' => 'INTEGER',
            'size' => null,
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
        'comment_id',    ];

    public const COMPOSITE_KEY = false;

    public const AUTOINC_COLUMN = 'comment_id';

    public const AUTOINC_SEQUENCE = null;

    public const ROW_CLASS = CommentRow;
}