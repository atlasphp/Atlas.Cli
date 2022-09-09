<?php
namespace Atlas\Mapper\DataSource\Tagging;

use Atlas\Mapper\DataSource\Tag\TagRecord;
use Atlas\Mapper\DataSource\Thread\ThreadRecord;
use Atlas\Mapper\Related;

class TaggingRelated extends Related
{
    #[Related\ManyToOne]
    protected ?ThreadRecord $thread;

    #[Related\ManyToOne]
    protected ?TagRecord $tag;
}
