<?php
namespace Atlas\Mapper\DataSource\Thread;

use Atlas\Mapper\DataSource\Author\AuthorRecord;
use Atlas\Mapper\DataSource\Reply\ReplyRecordSet;
use Atlas\Mapper\DataSource\Summary\SummaryRecord;
use Atlas\Mapper\DataSource\Tag\TagRecordSet;
use Atlas\Mapper\DataSource\Tagging\TaggingRecordSet;
use Atlas\Mapper\Related;

class ThreadRelated extends Related
{
    #[Related\ManyToOne]
    protected ?AuthorRecord $author;

    #[Related\OneToOne]
    #[Related\OnDelete('initDeleted')]
    protected ?SummaryRecord $summary;

    #[Related\OneToMany]
    #[Related\OnDelete('setDelete')]
    protected ReplyRecordSet $replies;

    #[Related\OneToMany]
    #[Related\OnDelete('setNull')]
    protected TaggingRecordSet $taggings;

    #[Related\ManyToMany(through: 'taggings')]
    protected TagRecordSet $tags;
}
