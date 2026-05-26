<?php

declare(strict_types=1);

namespace App\Model\DTO;

use DateTimeInterface;

/**
 * Data Transfer Object (DTO) pro příspěvek.
 * Slouží k bezpečnému přenosu dat mezi vrstvami aplikace bez závislosti na databázi.
 */
final class PostDto
{
    /**
     * @param int $id Unikátní identifikátor příspěvku
     * @param string $title Titulek příspěvku
     * @param string $content Hlavní textový obsah
     * @param DateTimeInterface|null $createdAt Datum a čas vytvoření
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $content,
        public ?DateTimeInterface $createdAt,
    ) {
    }
}
