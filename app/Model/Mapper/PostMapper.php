<?php

declare(strict_types=1);

namespace App\Model\Mapper;

use App\Model\DTO\PostDto;
use DateTimeInterface;
use Nette\Database\Table\ActiveRow;
use RuntimeException;

/**
 * Mapper zodpovědný za transformaci databázových řádků (ActiveRow) na čisté objekty (PostDto).
 * Tím se odděluje databázová struktura od zbytku aplikace.
 */
final class PostMapper
{
    /**
     * Mapuje jeden ActiveRow na PostDto.
     */
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
     * Mapuje kolekci ActiveRow na pole DTO objektů.
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

    /**
     * Pomocná metoda pro validaci a převod na int.
     */
    private function toInt(mixed $value): int
    {
        if (!is_int($value) && !is_string($value)) {
            throw new RuntimeException('Databázová hodnota nemá očekávaný celočíselný typ.');
        }

        return (int) $value;
    }

    /**
     * Pomocná metoda pro validaci a převod na string.
     */
    private function toString(mixed $value): string
    {
        if (!is_scalar($value)) {
            throw new RuntimeException('Databázová hodnota nemá očekávaný textový typ.');
        }

        return (string) $value;
    }
}
