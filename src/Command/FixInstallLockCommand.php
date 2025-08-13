<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\IOException;
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

        $helper = $this->getHelper('question');

        $filesystem = new Filesystem();

        if ($filesystem->exists(__DIR__.'/../../var/install.lock')) {
            $io->info('The installation is currently locked.');
            $confirmQuestion = new Question('Are you sure you want to unlock the installation? (y/n)', 'n');

            $confirm = $helper->ask($input, $output, $confirmQuestion);

            if ('y' === $confirm) {
                try {
                    $filesystem->remove(__DIR__.'/../../var/install.lock');
                } catch (IOException) {
                    $io->error('Something went wrong and the installation was not unlocked.');

                    return Command::FAILURE;
                }

                $io->success('Installation has been unlocked.');

                return Command::SUCCESS;
            }
        } else {
            $io->info('The installation is currently unlocked.');
            $confirmQuestion = new Question('Are you sure you want to lock the installation? (y/n)', 'n');

            $confirm = $helper->ask($input, $output, $confirmQuestion);

            if ('y' === $confirm) {
                try {
                    $filesystem->touch(__DIR__.'/../../var/install.lock');
                } catch (IOException) {
                    $io->error('Something went wrong and the installation was not locked.');

                    return Command::FAILURE;
                }

                $io->success('Installation has been locked.');

                return Command::SUCCESS;
            }
        }

        $io->text('Aborting...');

        return Command::FAILURE;
    }
}
