<?php
namespace Atlas\Mapper\DataSource\Bidibar;

use Atlas\Mapper\DataSource\Bidifoo\BidifooRecord;
use Atlas\Mapper\Related;

class BidibarRelated extends Related
{
    #[Related\OneToOneBidi(on: ['bidifoo_id' => 'bidifoo_id'])]
    protected ?BidifooRecord $bidifoo;
}
