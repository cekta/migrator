<?php

declare(strict_types=1);

use Cekta\Migrator\Command\Migrate;
use Cekta\Migrator\Example\Migration1;
use Cekta\Migrator\Example\Migration3;
use Cekta\Migrator\Example\MigrationMagic;
use Cekta\Migrator\Migration;
use Cekta\Migrator\MigrationLocator;
use Cekta\Migrator\Persist\DB;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$dsn = 'pgsql:host=pgsql;dbname=app';
$username = 'postgres';

$dsn = 'mysql:host=mysql;dbname=app;charset=utf8';
$username = 'root';

//$dsn = 'sqlite:db.sqlite';
//$username = null;

$pdo = new PDO($dsn, $username, '12345', [
    PDO::ATTR_EMULATE_PREPARES => false
]);
$locator = new class($pdo) implements MigrationLocator {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get(string $id): Migration
    {
        // psr/container can help load
        $result = match ($id) {
            Migration1::class => new Migration1($this->pdo),
            Migration3::class => new Migration3($this->pdo),
            MigrationMagic::class => new MigrationMagic($this->pdo),
            default => throw new InvalidArgumentException("Invalid id: `{$id}`"),
        };
        return $result;
    }
};
$application = new Application();
$application->add(
    new Migrate(new DB($pdo), $locator, [
        Migration1::class,
        Migration3::class,
        MigrationMagic::class,
    ])
);
$application->run();