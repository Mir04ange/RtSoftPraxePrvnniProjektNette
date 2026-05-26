<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use RuntimeException;

/**
 * Základní repozitář poskytující společné CRUD operace pro všechny ostatní repozitáře.
 * Implementuje princip DRY (Don't Repeat Yourself) pro práci s databází.
 */
abstract class BaseRepository
{
    /**
     * @param Explorer $database Nette Database Explorer pro dotazování
     */
    public function __construct(
        protected Explorer $database,
    ) {
    }

    /**
     * Abstraktní metoda, kterou musí potomci implementovat pro určení názvu tabulky.
     */
    abstract protected function getTableName(): string;

    /**
     * Vrací Selection pro celou tabulku. Slouží jako základ pro další filtrování.
     */
    public function findAll(): Selection
    {
        return $this->database->table($this->getTableName());
    }

    /**
     * Vyhledá jeden konkrétní záznam podle primárního klíče.
     */
    public function getById(int $id): ?ActiveRow
    {
        return $this->findAll()->get($id);
    }

    /**
     * Vloží nový záznam do tabulky.
     * @param array<string, mixed> $data Data k vložení
     */
    public function insert(array $data): ActiveRow
    {
        $row = $this->findAll()->insert($data);

        if (!$row instanceof ActiveRow) {
            throw new RuntimeException('Operace insert nevrátila očekávaný ActiveRow.');
        }

        return $row;
    }

    /**
     * Aktualizuje stávající záznam v tabulce.
     * @param int $id ID záznamu k aktualizaci
     * @param array<string, mixed> $data Nová data
     */
    public function update(int $id, array $data): ?ActiveRow
    {
        $row = $this->getById($id);

        if ($row === null) {
            return null;
        }

        $row->update($data);

        return $row;
    }

    /**
     * Smaže záznam podle primárního klíče.
     */
    public function delete(int $id): bool
    {
        $row = $this->getById($id);

        if ($row === null) {
            return false;
        }

        $row->delete();

        return true;
    }

    /**
     * Metoda Save inteligentně rozhoduje mezi vložením nového záznamu a aktualizací stávajícího.
     * Pokud je $id null, provede insert, jinak update.
     * 
     * @param int|null $id Primární klíč záznamu (null pro nový záznam)
     * @param array<string, mixed> $data Data k uložení
     */
    public function save(?int $id, array $data): ActiveRow
    {
        if ($id === null) {
            return $this->insert($data);
        }

        $row = $this->update($id, $data);

        if ($row === null) {
            throw new RuntimeException(sprintf('Záznam s ID %d nebyl v tabulce %s nalezen.', $id, $this->getTableName()));
        }

        return $row;
    }
}
