<?php

declare(strict_types=1);

namespace Cekta\Migrator;

use ReflectionClass;

class Module implements \Cekta\Module\Module
{
    /**
     * @var array<string,array<string>>
     */
    private array $state = [];

    public function __construct(
        private readonly string $storage = Storage\DB::class
    ) {
    }

    /**
     * @inheritDoc
     */
    public function onCreateParameters(mixed $cachedData): array
    {
        return [
            '...' . MigrationLocator::class . '$migrations' => $cachedData[Migration::class] ?? [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuildDefinitions(mixed $cachedData): array
    {
        return [
            'entries' => [
                ...($cachedData[Migration::class] ?? []),
            ],
            'alias' => [
                Storage::class => $this->storage,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function discover(ReflectionClass $class): void
    {
        if (
            $class->implementsInterface(Migration::class)
            && $class->isInstantiable()
        ) {
            $this->state[Migration::class][] = $class->name;
        }
    }

    /**
     * @inheritDoc
     */
    public function getCacheableData(): mixed
    {
        return $this->state;
    }
}
