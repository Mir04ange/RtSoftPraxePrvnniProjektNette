<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table\Selection;

final class CommentRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'comments';
    }

    public function findByPostId(int $postId): Selection
    {
        return $this->findAll()
            ->where('post_id', $postId)
            ->order('created_at DESC');
    }
}
