<?php
namespace Atlas\Mapper\DataSource\Bidifoo;

use Atlas\Mapper\DataSource\Bidibar\BidibarRecord;
use Atlas\Mapper\Related;

class BidifooRelated extends Related
{
    #[Related\OneToOneBidi(on: ['bidibar_id' => 'bidibar_id'])]
    protected ?BidibarRecord $bidibar;
}
