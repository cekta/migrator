<?php

declare(strict_types=1);

namespace Cekta\Migrator;

interface Migration
{
    public function up(): void;

    /**
     * @return int unix time stamp or any version number
     */
    public function order(): int;
}
