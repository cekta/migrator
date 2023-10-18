<?php

declare(strict_types=1);

namespace Cekta\Migrator\Test\Example;

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

    public function down(): void
    {
        $this->pdo->exec(
            <<<'EOF'
drop table test3;
EOF
        );
    }

    public static function id(): int
    {
        return 3;
    }
}
