<?php

declare(strict_types=1);

namespace Cekta\Migrator\Command;

use Cekta\Migrator\MigrationLocator;
use Cekta\Migrator\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate extends Command
{
    private Storage $storage;
    private MigrationLocator $locator;

    public function __construct(
        Storage $storage,
        MigrationLocator $locator,
        string $name = 'migrate'
    ) {
        parent::__construct($name);
        $this->storage = $storage;
        $this->locator = $locator;
    }

    protected function configure(): void
    {
        $this->addOption('install', 'i', description: 'Install persist storage if not installed');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->getOption('install') && !$this->storage->isInstalled()) {
            $this->storage->install();
        }
        if (!$input->getOption('install') && !$this->storage->isInstalled()) {
            $output->writeln('migrator not installed');
            $output->writeln('use -i or --install to install');
            return Command::FAILURE;
        }
        $ids = $this->storage->generateToExecuteIds($this->locator->ids());
        if (empty($ids)) {
            $output->writeln('nothing to migrate');
            return Command::SUCCESS;
        }

        $output->writeln('start');
        foreach ($ids as $id) {
            $migration = $this->locator->get($id);
            $class = get_class($migration);
            $output->writeln("{$class} started");
            $this->storage->execute($migration);
            $output->writeln("{$class} executed");
        }
        $output->writeln('done');
        return Command::SUCCESS;
    }
}
