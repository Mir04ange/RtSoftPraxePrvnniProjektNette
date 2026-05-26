<?php

declare(strict_types=1);

namespace App\Model\DTO;

use DateTimeInterface;

final class CommentDto
{
    public function __construct(
        public int $id,
        public int $postId,
        public string $name,
        public string $email,
        public string $content,
        public ?DateTimeInterface $createdAt,
    ) {
    }
}
