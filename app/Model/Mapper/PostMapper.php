<?php

declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\PostDto;
use DateTimeInterface;
use Nette\Database\Table\ActiveRow;
use RuntimeException;

final class PostMapper
{
    public function map(ActiveRow $row): PostDto
    {
        $createdAt = $row->created_at;

        return new PostDto(
            id: $this->toInt($row->id),
            title: $this->toString($row->title),
            content: $this->toString($row->content),
            createdAt: $createdAt instanceof DateTimeInterface ? $createdAt : null,
        );
    }

    /**
     * @param iterable<ActiveRow> $rows
     * @return list<PostDto>
     */
    public function mapMany(iterable $rows): array
    {
        $posts = [];

        foreach ($rows as $row) {
            $posts[] = $this->map($row);
        }

        return $posts;
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
