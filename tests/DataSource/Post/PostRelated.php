<?php
namespace Atlas\Mapper\DataSource\Post;

use Atlas\Mapper\DataSource\Comment\CommentRecordSet;
use Atlas\Mapper\Related;

class PostRelated extends Related
{
    #[Related\OneToMany(on: ['post_id' => 'related_id'])]
    #[Related\Where('related_type = ', 'post')]
    protected CommentRecordSet $comments;
}
