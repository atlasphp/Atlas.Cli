<?php
declare(strict_types=1);

namespace {NAMESPACE}\{TYPE};

use Atlas\Mapper\RecordSet;

/**
 * @method {TYPE}Record offsetGet($offset)
 * @method {TYPE}Record appendNew(array $fields = [])
 * @method {TYPE}Record|null getOneBy(array $whereEquals)
 * @method {TYPE}RecordSet getAllBy(array $whereEquals)
 * @method {TYPE}Record|null detachOneBy(array $whereEquals)
 * @method {TYPE}RecordSet detachAllBy(array $whereEquals)
 * @method {TYPE}RecordSet detachAll()
 * @method {TYPE}RecordSet detachDeleted()
 */
class {TYPE}RecordSet extends RecordSet
{
}
