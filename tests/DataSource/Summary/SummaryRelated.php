<?php
namespace Atlas\Mapper\DataSource\Summary;

use Atlas\Mapper\DataSource\Thread\ThreadRecord;
use Atlas\Mapper\Related;

class SummaryRelated extends Related
{
    #[Related\OneToOne]
    protected ?ThreadRecord $thread;
}
