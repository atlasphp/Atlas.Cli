<?php
namespace Atlas\Mapper\DataSource\Reply;

use Atlas\Mapper\DataSource\Author\AuthorRecord;
use Atlas\Mapper\Related;

class ReplyRelated extends Related
{
    #[Related\ManyToOne]
    protected ?AuthorRecord $author;
}
