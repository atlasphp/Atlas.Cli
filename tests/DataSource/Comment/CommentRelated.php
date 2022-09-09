<?php
namespace Atlas\Mapper\DataSource\Comment;

use Atlas\Mapper\DataSource\Page\PageRecord;
use Atlas\Mapper\DataSource\Post\PostRecord;
use Atlas\Mapper\DataSource\Video\VideoRecord;
use Atlas\Mapper\Related;

class CommentRelated extends Related
{
    #[Related\ManyToOneVariant(column: 'related_type')]
    #[Related\Variant(value: 'page',  class: PageRecord::CLASS,  on: ['related_id' => 'page_id'])]
    #[Related\Variant(value: 'post',  class: PostRecord::CLASS,  on: ['related_id' => 'post_id'])]
    #[Related\Variant(value: 'video', class: VideoRecord::CLASS, on: ['related_id' => 'video_id'])]
    protected mixed $commentable;
}
