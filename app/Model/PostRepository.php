<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Table\Selection;

final class PostRepository extends BaseRepository
{
    protected function getTableName(): string
    {
        return 'posts';
    }

    public function findPublicPosts(): Selection
    {
        return $this->findAll()->order('created_at DESC');
    }
}
