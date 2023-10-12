<?php

declare(strict_types=1);

namespace Cekta\Migrator\Persist;

use Cekta\Migrator\Migration;
use Cekta\Migrator\Persist;
use InvalidArgumentException;
use PDO;
use PDOException;
use Throwable;

class DB implements Persist
{
    private PDO $pdo;
    private string $table_name;
    private string $column_name;

    /**
     * @param PDO $pdo
     * @param string $table_name
     * @param string $column_name
     */
    public function __construct(PDO $pdo, string $table_name = 'migrations', string $column_name = 'name')
    {
        $this->pdo = $pdo;

        $safe_pattern = '#^[a-zA-Z0-9_-]*$#';
        if (!preg_match($safe_pattern, $table_name)) {
            throw new InvalidArgumentException(
                "Invalid table_name: {$table_name} must match pattern: {$safe_pattern}"
            );
        }
        $this->table_name = $table_name;

        if (!preg_match($safe_pattern, $column_name)) {
            throw new InvalidArgumentException(
                "Invalid column_name: {$column_name} must match pattern: {$safe_pattern}"
            );
        }
        $this->column_name = $column_name;
    }

    public function generateToExecuteIds(array $ids): array
    {
        $sth = $this->pdo->query("SELECT * FROM {$this->table_name}");
        if ($sth === false) {
            throw new PDOException(
                "[{$this->pdo->errorInfo()[0]}][{$this->pdo->errorInfo()[1]}] {$this->pdo->errorInfo()[2]}"
            );
        }

        $result = [];
        foreach ($sth as $row) {
            $result[] = $row[$this->column_name];
        }

        return array_diff($ids, $result);
    }

    public function execute(Migration $migration): void
    {
        $migration->up();
        $sth = $this->pdo->prepare(
            "INSERT INTO {$this->table_name} 
                ({$this->column_name}) 
                VALUES (?)"
        );
        $sth->execute([$migration->id()]);
    }

    public function isInstalled(): bool
    {
        try {
            $result = $this->pdo->query("SELECT 1 FROM {$this->table_name} LIMIT 1");
        } catch (Throwable) {
            return false;
        }
        return $result !== false;
    }

    public function install(): void
    {
        $sql = "create table {$this->table_name}
(
    {$this->column_name} varchar(255) not null primary key
);";
        $this->pdo->exec($sql);
    }
}
