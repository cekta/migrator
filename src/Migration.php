<?php

declare(strict_types=1);

namespace Cekta\Migrator;

interface Migration
{
    public function up(): void;

    /**
     * lower more priority
     * @return int
     */
    public function order(): int;

    /**
     * uniq migration id
     * @return string
     */
    public function id(): string;
}
