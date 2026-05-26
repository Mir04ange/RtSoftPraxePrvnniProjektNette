<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

final class CommentRepository
{
    public function __construct(
        public Nette\Database\Explorer $database,
    ) {
    }

    private function findAll()
    {
        return $this->database->table('comments');
    }

    public function findCommentsByPostId(int $postId)
    {
        return $this->findAll()
            ->where('post_id', $postId)
            ->order('created_at DESC'); // Seřadí od nejnovějších
    }

    public function insert(iterable $data)
    {
        return $this->findAll()->insert($data);
    }

    public function delete(int $id): void
    {
        $comment = $this->findAll()->get($id);
        if ($comment) {
            $comment->delete();
        }
    }

    public function findByPost(int $id)
    {
        if ($comment = $this->findAll()->get($id)) {
            return $comment;
        }
        return null;

    }

}