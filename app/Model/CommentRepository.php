<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

final class CommentRepository
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    public function findAll()
    {
        return $this->database->table('comments');
    }

    public function insert(iterable $data)
    {
        return $this->findAll()->insert($data);
    }

    public function findByPost(int $postId)
    {
        return $this->findAll()->where('post_id', $postId)->order('created_at');
    }
}
