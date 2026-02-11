<?php

declare(strict_types=1);

namespace Cekta\Migrator;

interface Storage
{
    public function execute(Migration $migration): void;

    /**
     * @param array<int> $ids
     * @return array<int>
     */
    public function generateToExecuteIds(array $ids): array;

    public function isInstalled(): bool;

    public function install(): void;

    /**
     * @return array<int>
     */
    public function getRollbackIds(int $step = 1): array;

    public function rollback(Migration $migration): void;
}
