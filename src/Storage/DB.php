<?php

declare(strict_types=1);

namespace Cekta\Migrator\Storage;

use Cekta\Migrator\Migration;
use Cekta\Migrator\Storage;
use InvalidArgumentException;
use PDO;
use PDOException;
use Throwable;

class DB implements Storage
{
    private PDO $pdo;
    private string $table_name;
    private string $column_id;
    private string $column_class;

    public function __construct(
        PDO $pdo,
        string $table_name = 'migrations',
        string $column_id = 'id',
        string $column_class = 'class'
    ) {
        $this->pdo = $pdo;

        $safe_pattern = '#^[a-zA-Z0-9_-]*$#';
        if (!preg_match($safe_pattern, $table_name)) {
            throw new InvalidArgumentException(
                "Invalid table_name: {$table_name} must match pattern: {$safe_pattern}"
            );
        }
        $this->table_name = $table_name;

        if (!preg_match($safe_pattern, $column_id)) {
            throw new InvalidArgumentException(
                "Invalid column_name: {$column_id} must match pattern: {$safe_pattern}"
            );
        }
        $this->column_id = $column_id;

        if (!preg_match($safe_pattern, $column_class)) {
            throw new InvalidArgumentException(
                "Invalid column_name: {$column_class} must match pattern: {$safe_pattern}"
            );
        }
        $this->column_class = $column_class;
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
            $result[] = $row[$this->column_id];
        }

        $ids = array_diff($ids, $result);
        sort($ids);

        return $ids;
    }

    public function execute(Migration $migration): void
    {
        $migration->up();
        $sth = $this->pdo->prepare(
            "INSERT INTO {$this->table_name} 
                ({$this->column_id}, {$this->column_class}) 
                VALUES (?, ?)"
        );
        $sth->execute([$migration->id(), get_class($migration)]);
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
    {$this->column_id} bigint not null primary key,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    {$this->column_class} varchar(2048) not null
);";
        $this->pdo->exec($sql);
    }

    public function getRollbackIds($step = 1): array
    {
        $sth = $this->pdo->query(
            "select * from {$this->table_name} ORDER BY {$this->column_id} desc LIMIT {$step}"
        );

        $result = [];
        foreach ($sth as $row) {
            $result[] = $row[$this->column_id];
        }
        return $result;
    }

    public function rollback(Migration $migration): void
    {
        $migration->down();
        $sth = $this->pdo->prepare("DELETE FROM {$this->table_name} WHERE {$this->column_id} = ?");
        $sth->execute([$migration::id()]);
    }
}
