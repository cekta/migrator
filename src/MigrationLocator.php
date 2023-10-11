<?php

declare(strict_types=1);

namespace Cekta\Migrator;

interface MigrationLocator
{
    public function get(string $id): Migration;
}
