<?php

declare(strict_types=1);

namespace Cekta\Migrator;

use ReflectionClass;

class Module implements \Cekta\Framework\Contract\Module
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
    public function onCreate(string $encoded_module): array
    {
        /** @var array<string,array<string>> $state */
        $state = json_decode($encoded_module, true);
        return [
            '...' . MigrationLocator::class . '$migrations' => $state[Migration::class] ?? [],
        ];
    }

    /**
     * @inheritDoc
     */
    public function onBuild(string $encoded_module): array
    {
        /** @var array<string,array<string>> $state */
        $state = json_decode($encoded_module, true);
        return [
            'entries' => [
                ...($state[Migration::class] ?? []),
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
    public function getEncodedModule(): string
    {
        $encoded_module = json_encode($this->state, JSON_PRETTY_PRINT);
        if ($encoded_module === false) {
            throw new \RuntimeException('state not encoded');
        }
        return $encoded_module;
    }
}
