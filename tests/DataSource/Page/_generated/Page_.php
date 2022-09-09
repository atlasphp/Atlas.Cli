<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Mapper\DataSource\Page\_generated;

use Atlas\Mapper\Mapper;
use Atlas\Mapper\DataSource\Page\PageRecord;
use Atlas\Mapper\DataSource\Page\PageRecordSet;
use Atlas\Mapper\DataSource\Page\PageSelect;
use Atlas\Mapper\DataSource\Page\PageTable;

/**
 * @method PageTable getTable()
 * @method ?PageRecord fetchRecord(mixed $primaryVal, array $loadRelated = [])
 * @method ?PageRecord fetchRecordBy(array $whereEquals, array $loadRelated = [])
 * @method PageRecord[] fetchRecords(array $primaryVals, array $loadRelated = [])
 * @method PageRecord[] fetchRecordsBy(array $whereEquals, array $loadRelated = [])
 * @method PageRecordSet fetchRecordSet(array $primaryVals, array $loadRelated = [])
 * @method PageRecordSet fetchRecordSetBy(array $whereEquals, array $loadRelated = [])
 * @method PageSelect select(array $whereEquals = [])
 * @method PageRecord newRecord(array $fields = [])
 * @method PageRecord[] newRecords(array $fieldSets)
 * @method PageRecordSet newRecordSet(array $records = [])
 * @method PageRecord turnRowIntoRecord(PageRow $row, array $loadRelated = [])
 * @method PageRecord[] turnRowsIntoRecords(array $rows, array $loadRelated = [])
 */
abstract class Page_ extends Mapper
{
}