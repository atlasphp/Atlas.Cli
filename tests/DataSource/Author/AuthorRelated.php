<?php
namespace Atlas\Mapper\DataSource\Author;

use Atlas\Mapper\DataSource\Reply\ReplyRecordSet;
use Atlas\Mapper\DataSource\Thread\ThreadRecordSet;
use Atlas\Mapper\Related;

class AuthorRelated extends Related
{
    #[Related\OneToMany]
    protected ReplyRecordSet $replies;

    #[Related\OneToMany]
    protected ThreadRecordSet $threads;
}
