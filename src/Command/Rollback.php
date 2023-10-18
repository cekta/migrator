<?php

declare(strict_types=1);

namespace Cekta\Migrator\Command;

use Cekta\Migrator\MigrationLocator;
use Cekta\Migrator\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Rollback extends Command
{
    private Storage $storage;
    private MigrationLocator $locator;

    public function __construct(
        Storage $storage,
        MigrationLocator $locator,
        string $name = 'migration:rollback'
    ) {
        parent::__construct($name);
        $this->storage = $storage;
        $this->locator = $locator;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->storage->isInstalled()) {
            $output->writeln('migrator not installed');
            return Command::FAILURE;
        }
        $ids = $this->storage->getRollbackIds();

        if (empty($ids)) {
            $output->writeln('nothing to rollback');
            return Command::SUCCESS;
        }

        $output->writeln('start');
        foreach ($ids as $id) {
            $migration = $this->locator->get($id);
            $class = get_class($migration);
            $output->writeln("{$class} started");
            $this->storage->rollback($migration);
            $output->writeln("{$class} rollbacked");
        }
        $output->writeln('done');
        return Command::SUCCESS;
    }
}
