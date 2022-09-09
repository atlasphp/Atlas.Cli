<?php
namespace Atlas\Mapper\DataSource\Video;

use Atlas\Mapper\DataSource\Comment\CommentRecordSet;
use Atlas\Mapper\Related;

class VideoRelated extends Related
{
    #[Related\OneToMany(on: ['video_id' => 'related_id'])]
    #[Related\Where('related_type = ', 'video')]
    protected CommentRecordSet $comments;
}
