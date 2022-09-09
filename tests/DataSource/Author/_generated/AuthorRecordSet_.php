<?php
/**
 * This file was generated by Atlas. Changes will be overwritten.
 */
declare(strict_types=1);

namespace Atlas\Mapper\DataSource\Author\_generated;

use Atlas\Mapper\RecordSet;
use Atlas\Mapper\DataSource\Author\AuthorRecord;
use Atlas\Mapper\DataSource\Author\AuthorRecordSet;

/**
 * @method __construct(AuthorRecord[], callable $newRecordFactory)
 * @method AuthorRecord offsetGet($offset)
 * @method AuthorRecord appendNew(array $fields = [])
 * @method ?AuthorRecord getOneBy(array $whereEquals)
 * @method AuthorRecordSet getAllBy(array $whereEquals)
 * @method ?AuthorRecord detachOneBy(array $whereEquals)
 * @method AuthorRecordSet detachAllBy(array $whereEquals)
 * @method AuthorRecordSet detachAll()
 * @method AuthorRecordSet detachDeleted()
 */
abstract class AuthorRecordSet_ extends RecordSet
{
}