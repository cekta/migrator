<?php

declare(strict_types=1);

namespace Cekta\Migrator\Command;

use Cekta\Migrator\Migration;
use Cekta\Migrator\MigrationLocator;
use Cekta\Migrator\Persist;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    private Persist $persist;
    /**
     * @var array<string>
     */
    private array $ids;
    private MigrationLocator $locator;

    /**
     * @param Persist $persist
     * @param MigrationLocator $locator
     * @param array<string> $ids
     * @param string $name
     */
    public function __construct(
        Persist $persist,
        MigrationLocator $locator,
        array $ids,
        string $name = 'migrate'
    ) {
        parent::__construct($name);
        $this->persist = $persist;
        $this->ids = $ids;
        $this->locator = $locator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ids = array_diff($this->ids, $this->persist->getExecutedIds());
        if (empty($ids)) {
            $output->writeln('nothing to migrate');
            return Command::SUCCESS;
        }
        $migrations = $this->loadMigrations($ids);
        ksort($migrations);
        $output->writeln('start');
        try {
            foreach ($migrations as $migration) {
                $output->writeln("{$migration->id()} started");
                $this->persist->execute($migration);
                $output->writeln("{$migration->id()} executed");
            }
        } catch (\Throwable $throwable) {
            $output->writeln("can`t execute migration {$migration->id()}");
            $output->writeln((string)$throwable);
            return Command::FAILURE;
        }
        $output->writeln('done');
        return Command::SUCCESS;
    }

    /**
     * @param array<string> $ids
     * @return array<Migration>
     */
    private function loadMigrations(array $ids): array
    {
        $migrations = [];
        foreach ($ids as $id) {
            $migration = $this->locator->get($id);
            if (array_key_exists($migration->order(), $migrations)) {
                throw new RuntimeException("order `{$migration->order()}` duplication for `{$id}`");
            }

            if ($migration->id() !== $id) {
                throw new RuntimeException(
                    "Loaded migration with id: {$migration->id()} must be equal id: {$id}"
                );
            }
            $migrations[$migration->order()] = $migration;
        }
        return $migrations;
    }
}
