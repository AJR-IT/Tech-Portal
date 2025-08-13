<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:recover-admin',
    description: 'Recover an admin account',
)]
class RecoverAdminUserCommand extends Command
{
    private SymfonyStyle $io;

    protected array $defaultCredenetials = [
        'username' => 'defaultadmin',
        'email' => 'defaultadmin@localhost',
        'plainPassword' => 'changeme',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp($this->getCommandHelp())
            ->addArgument('username', InputArgument::OPTIONAL, 'The username of the user you want to recover')
            ->addArgument('password', InputArgument::OPTIONAL, 'The password of the user you want to recover')
            ->addOption('asAdmin', null, InputOption::VALUE_NONE, 'If the user is not currently an admin, this will elevate to admin')
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->io = new SymfonyStyle($input, $output);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $username */
        $username = $input->getArgument('username') ?? $this->defaultCredenetials['username'];

        /** @var string $plainPassword */
        $plainPassword = $input->getArgument('password') ?? $this->defaultCredenetials['plainPassword'];

        $user = $this->userRepository->findOneBy(['username' => $username]);

        if (!$user instanceof User) {
            $user = new User();
            $user->setEmail($this->defaultCredenetials['email']);
            $user->setDateCreated(new \DateTimeImmutable());
            $user->setUsername($username);
            $user->setRoles(['ROLE_ADMIN']);
        }

        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );

        $asAdmin = $input->getOption('asAdmin');

        if ($asAdmin) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->io->success(
            sprintf('User "%s" was successfully recovered with password "%s".',
                $user->getUsername(),
                $plainPassword
            )
        );

        return Command::SUCCESS;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $this->io->title('Initialize Users database');
        $this->io->text([
            'WARNING: This command is used to recover an admin account.',
            '',
            'If you do not provide a username, it will create a default admin account.',
            '',
            'If you omit the password, a default will be provided.',
        ]);
    }

    private function getCommandHelp(): string
    {
        return <<<'HELP'
            The <info>%command.name%</info> command can recover an admin account.

                <info>php bin/console %command.full_name%</info> <comment>username password</comment>

            By default the command will create an admin account. If you provide a username,
            the command will reset the password of that user.

            Furthermore, you can supply the <comment>--asAdmin</comment> option to elevate that user to admin status.
        HELP;
    }
}
