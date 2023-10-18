<?php

declare(strict_types=1);

namespace Cekta\Migrator;

interface Storage
{
    public function execute(Migration $migration): void;

    public function generateToExecuteIds(array $ids): array;

    public function isInstalled(): bool;

    public function install(): void;

    public function getRollbackIds(): array;

    public function rollback(Migration $migration): void;
}
