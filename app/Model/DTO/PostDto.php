<?php

declare(strict_types=1);

namespace App\Model\DTO;

use DateTimeInterface;

final class PostDto
{
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public ?DateTimeInterface $createdAt,
    ) {
    }
}
