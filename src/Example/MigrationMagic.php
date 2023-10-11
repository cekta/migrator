<?php

declare(strict_types=1);

namespace Cekta\Migrator\Example;

use Cekta\Migrator\Migration;
use PDO;

class MigrationMagic implements Migration
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function up(): void
    {
        $this->pdo->exec(
            <<<'EOF'
create table test2
(
    id int auto_increment,
    constraint test1_pk
        primary key (id)
);
EOF

        );
    }

    public function order(): int
    {
        return 1696191474;
    }

    public function id(): string
    {
        return get_class($this);
    }
}
