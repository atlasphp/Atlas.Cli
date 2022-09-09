<?php
namespace Atlas\Mapper\DataSource\Tag;

use Atlas\Mapper\DataSource\Tagging\TaggingRecordSet;
use Atlas\Mapper\Related;

class TagRelated extends Related
{
    #[Related\OneToMany]
    #[Related\OnDelete('cascade')]
    protected TaggingRecordSet $taggings;
}
