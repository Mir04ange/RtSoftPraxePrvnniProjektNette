<?php

declare(strict_types=1);

namespace App\Model;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use RuntimeException;

abstract class BaseRepository
{
    public function __construct(
        protected Explorer $database,
    ) {
    }

    abstract protected function getTableName(): string;

    public function findAll(): Selection
    {
        return $this->database->table($this->getTableName());
    }

    public function getById(int $id): ?ActiveRow
    {
        return $this->findAll()->get($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function insert(array $data): ActiveRow
    {
        $row = $this->findAll()->insert($data);

        if (!$row instanceof ActiveRow) {
            throw new RuntimeException('Insert operation did not return an ActiveRow.');
        }

        return $row;
    }

    /**
     * @param array<string, mixed> $data
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
     * @param array<string, mixed> $data
     */
    public function save(?int $id, array $data): ActiveRow
    {
        if ($id === null) {
            return $this->insert($data);
        }

        $row = $this->update($id, $data);

        if ($row === null) {
            throw new RuntimeException(sprintf('Row with ID %d was not found in table %s.', $id, $this->getTableName()));
        }

        return $row;
    }
}
