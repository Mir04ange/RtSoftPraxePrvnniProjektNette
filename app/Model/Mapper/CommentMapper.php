<?php

declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\CommentDto;
use DateTimeInterface;
use Nette\Database\Table\ActiveRow;
use RuntimeException;

final class CommentMapper
{
    public function map(ActiveRow $row): CommentDto
    {
        $createdAt = $row->created_at;

        return new CommentDto(
            id: $this->toInt($row->id),
            postId: $this->toInt($row->post_id),
            name: $this->toString($row->name),
            email: $this->toString($row->email),
            content: $this->toString($row->content),
            createdAt: $createdAt instanceof DateTimeInterface ? $createdAt : null,
        );
    }

    /**
     * @param iterable<ActiveRow> $rows
     * @return list<CommentDto>
     */
    public function mapMany(iterable $rows): array
    {
        $comments = [];

        foreach ($rows as $row) {
            $comments[] = $this->map($row);
        }

        return $comments;
    }

    private function toInt(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            throw new RuntimeException('Databázová hodnota nemá očekávaný celočíselný typ.');
        }

        return (int) $value;
    }

    private function toString(mixed $value): string
    {
        if (!is_scalar($value)) {
            throw new RuntimeException('Databázová hodnota nemá očekávaný textový typ.');
        }

        return (string) $value;
    }
}
