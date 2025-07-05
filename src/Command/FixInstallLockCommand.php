<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:fix-install-lock',
    description: 'Fix the installation lock. If the installation lock ever breaks, this will lock the installer again.',
)]
class FixInstallLockCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $filesystem = new Filesystem();

        $filesystem->touch(__DIR__ . '/../../var/lock.lock');

        if ($filesystem->exists(__DIR__ . '/../../var/lock.lock')) {
            $io->success('Installation has been locked.');

            return Command::SUCCESS;
        }

        $io->error('Something went wrong and the install was not locked.');

        return Command::FAILURE;
    }
}
