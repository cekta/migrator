<?php

declare(strict_types=1);

namespace Cekta\Migrator;

interface Persist
{
    public function execute(Migration $migration): void;

    public function generateToExecuteIds(array $ids): array;

    public function isInstalled(): bool;
    public function install(): void;
}
