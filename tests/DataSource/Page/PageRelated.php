<?php
namespace Atlas\Mapper\DataSource\Page;

use Atlas\Mapper\DataSource\Comment\CommentRecordSet;
use Atlas\Mapper\Related;

class PageRelated extends Related
{
    #[Related\OneToMany(on: ['page_id' => 'related_id'])]
    #[Related\Where('related_type = ', 'page')]
    protected CommentRecordSet $comments;
}
