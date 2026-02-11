<?php

declare(strict_types=1);

namespace Cekta\Migrator;

use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RuntimeException;

class MigrationLocator
{
    /**
     * @var string[]
     */
    private array $migrations = [];
    private ContainerInterface $container;

    /**
     * @param ContainerInterface $container
     * @param class-string ...$migrations
     */
    public function __construct(ContainerInterface $container, string ...$migrations)
    {
        foreach ($migrations as $fqcn) {
            if (!in_array(Migration::class, class_implements($fqcn))) {
                throw new InvalidArgumentException("{$fqcn} must implement " . Migration::class);
            }
            $id = $fqcn::id();
            if (array_key_exists($id, $this->migrations)) {
                throw new InvalidArgumentException(
                    "ID = `{$id}` is not equal, check: {$fqcn} and {$this->migrations[$id]}"
                );
            }
            $this->migrations[$id] = $fqcn;
        }
        $this->container = $container;
    }

    public function get(int $id): Migration
    {
        if (!array_key_exists($id, $this->migrations)) {
            $message = "Not found migration name for id = `{$id}`";
            throw new class ($message) extends \RuntimeException implements NotFoundExceptionInterface {
            };
        }

        /** @var Migration $migration */
        $migration = $this->container->get($this->migrations[$id]);

        if ($migration->id() !== $id) {
            throw new RuntimeException(
                "Loaded migration with id: {$migration->id()} must be equal id: {$id}"
            );
        }

        return $migration;
    }

    /**
     * @return array<int>
     */
    public function ids(): array
    {
        return array_keys($this->migrations);
    }
}
