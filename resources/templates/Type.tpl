<?php
declare(strict_types=1);

namespace {NAMESPACE}\{TYPE};

use Atlas\Mapper\Mapper;
use Atlas\Table\Row;

/**
 * @method {TYPE}Table getTable()
 * @method {TYPE}Relationships getRelationships()
 * @method {TYPE}Record|null fetchRecord($primaryVal, array $with = [])
 * @method {TYPE}Record|null fetchRecordBy(array $whereEquals, array $with = [])
 * @method {TYPE}Record[] fetchRecords(array $primaryVals, array $with = [])
 * @method {TYPE}Record[] fetchRecordsBy(array $whereEquals, array $with = [])
 * @method {TYPE}RecordSet fetchRecordSet(array $primaryVals, array $with = [])
 * @method {TYPE}RecordSet fetchRecordSetBy(array $whereEquals, array $with = [])
 * @method {TYPE}Select select(array $whereEquals = [])
 * @method {TYPE}Record newRecord(array $fields = [])
 * @method {TYPE}Record[] newRecords(array $fieldSets)
 * @method {TYPE}RecordSet newRecordSet(array $records = [])
 * @method {TYPE}Record turnRowIntoRecord(Row $row, array $with = [])
 * @method {TYPE}Record[] turnRowsIntoRecords(array $rows, array $with = [])
 */
class {TYPE} extends Mapper
{
}
