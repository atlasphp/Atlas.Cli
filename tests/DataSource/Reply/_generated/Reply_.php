<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Mapper\DataSource\Reply\_generated;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\DataSource\Reply\ReplyRecord;
use Atlas\Mapper\DataSource\Reply\ReplyRecordSet;
use Atlas\Mapper\DataSource\Reply\ReplySelect;
use Atlas\Mapper\DataSource\Reply\ReplyTable;

/**
 * @method ReplyTable getTable()
 * @method ?ReplyRecord fetchRecord(mixed $primaryVal, array $loadRelated = [])
 * @method ?ReplyRecord fetchRecordBy(array $whereEquals, array $loadRelated = [])
 * @method ReplyRecord[] fetchRecords(array $primaryVals, array $loadRelated = [])
 * @method ReplyRecord[] fetchRecordsBy(array $whereEquals, array $loadRelated = [])
 * @method ReplyRecordSet fetchRecordSet(array $primaryVals, array $loadRelated = [])
 * @method ReplyRecordSet fetchRecordSetBy(array $whereEquals, array $loadRelated = [])
 * @method ReplySelect select(array $whereEquals = [])
 * @method ReplyRecord newRecord(array $fields = [])
 * @method ReplyRecord[] newRecords(array $fieldSets)
 * @method ReplyRecordSet newRecordSet(array $records = [])
 * @method ReplyRecord turnRowIntoRecord(ReplyRow $row, array $loadRelated = [])
 * @method ReplyRecord[] turnRowsIntoRecords(array $rows, array $loadRelated = [])
 */
abstract class Reply_ extends Mapper
{
}