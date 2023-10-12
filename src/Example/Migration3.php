<?php

declare(strict_types=1);

namespace Cekta\Migrator\Example;

use Cekta\Migrator\Migration;
use PDO;

class Migration3 implements Migration
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
create table test3
(
    id int
);
EOF

        );
    }

    public function order(): int
    {
        return 1696191220;
    }

    public function id(): string
    {
        return get_class($this);
    }
}
