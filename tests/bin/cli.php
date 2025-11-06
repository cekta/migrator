<?php

declare(strict_types=1);

use Cekta\Migrator\Command\MakeMigration;
use Cekta\Migrator\Command\Migrate;
use Cekta\Migrator\Command\Rollback;
use Cekta\Migrator\Migration;
use Cekta\Migrator\MigrationLocator;
use Cekta\Migrator\Storage\DB;
use Cekta\Migrator\Test\Example\Migration1;
use Cekta\Migrator\Test\Example\Migration3;
use Cekta\Migrator\Test\Example\MigrationMagic;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

require __DIR__ . '/../../vendor/autoload.php';

$dsn = 'pgsql:host=pgsql;dbname=app';
$username = 'postgres';

$dsn = 'mysql:host=mysql;dbname=app;charset=utf8';
$username = 'root';

$dsn = 'sqlite:db.sqlite';
$username = null;

$pdo = new PDO($dsn, $username, '12345', [
    PDO::ATTR_EMULATE_PREPARES => false
]);
$container = new class($pdo) implements ContainerInterface {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get(string $id): Migration
    {
        $result = match ($id) {
            Migration1::class => new Migration1($this->pdo),
            Migration3::class => new Migration3($this->pdo),
            MigrationMagic::class => new MigrationMagic($this->pdo),
            default => throw new InvalidArgumentException("Invalid id: `{$id}`"),
        };
        return $result;
    }

    public function has(string $id): bool
    {
        return true;
    }
};
$locator = new MigrationLocator($container, ...[Migration1::class, Migration3::class, MigrationMagic::class]);
$storage = new DB($pdo);
$application = new Application();
$application->addCommands([
    new Migrate($storage, $locator),
    new Rollback($storage, $locator),
    new MakeMigration(),
]);
$application->run();