<?php

declare(strict_types=1);

use Cekta\Migrator\Command\Migrate;
use Cekta\Migrator\Example\Migration1;
use Cekta\Migrator\Example\Migration3;
use Cekta\Migrator\Example\MigrationMagic;
use Cekta\Migrator\Persist\DB;
use Symfony\Component\Console\Application;

require __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('mysql:host=db;dbname=app;charset=utf8', 'root', '12345', [
    PDO::ATTR_EMULATE_PREPARES => false
]);
$migrations[] = new Migration1($pdo);
$migrations[] = new Migration3($pdo);
$migrations[] = new MigrationMagic($pdo);
$application = new Application();
$application->add(
    new Migrate(new DB($pdo), $migrations)
);
$application->run();