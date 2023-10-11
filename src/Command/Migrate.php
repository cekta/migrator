<?php

declare(strict_types=1);

namespace Cekta\Migrator\Command;

use Cekta\Migrator\Migration;
use Cekta\Migrator\Persist;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    private Persist $persist;
    /**
     * @var array<Migration>
     */
    private array $migrations;

    /**
     * @param Persist $persist
     * @param array<Migration> $migrations
     * @param string $name
     */
    public function __construct(Persist $persist, array $migrations, string $name = 'migrate')
    {
        parent::__construct($name);
        $this->persist = $persist;
        foreach ($migrations as $index => $migration) {
            if (!($migration instanceof Migration)) {
                throw new \InvalidArgumentException("invalid migrations with index: {$index}");
            }
            $name = get_class($migration);
            $this->migrations[$name] = $migration;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $executed_migrations = $this->persist->getExecutedMigrations();
        $migration_names = array_diff(array_keys($this->migrations), $executed_migrations);
        if (empty($migration_names)) {
            $output->writeln('nothing to migrate');
            return Command::SUCCESS;
        }
        $migrations = [];
        foreach ($migration_names as $name) {
            $migration = $this->migrations[$name];
            $name = get_class($this->migrations[$name]);
            if (array_key_exists($migration->order(), $migrations)) {
                throw new \RuntimeException("order `{$migration->order()}` duplication for `{$name}`");
            }
            $migrations[$migration->order()] = $migration;
        }

        ksort($migrations);

        $output->writeln('start');
        try {
            foreach ($migrations as $migration) {
                $name = get_class($migration);
                $output->writeln("{$name} started");
                $this->persist->execute($migration);
                $output->writeln("{$name} executed");
            }
        } catch (\Throwable $throwable) {
            $migration_name = get_class($migration);
            $output->writeln("can`t execute migration {$migration_name}");
            $output->writeln((string)$throwable);
            return Command::FAILURE;
        }
        $output->writeln('done');
        return Command::SUCCESS;
    }
}
