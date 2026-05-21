<?php

declare(strict_types=1);

namespace App\Model;

use Nette;

final class PostRepository
{
    public function __construct(
        private Nette\Database\Explorer $database,
    ) {
    }

    public function findAll()
    {
        return $this->database->table('posts');
    }

    public function findPublicPosts()
    {
        return $this->findAll()->order('created_at DESC');
    }

    public function getById(int $id)
    {
        return $this->findAll()->get($id);
    }

    public function insert(iterable $data)
    {
        return $this->findAll()->insert($data);
    }

    public function update(int $id, iterable $data)
    {
        $post = $this->getById($id);
        if ($post) {
            $post->update($data);
        }
        return $post;
    }
}
