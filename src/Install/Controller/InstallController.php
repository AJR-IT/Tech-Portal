<?php

declare(strict_types=1);

namespace App\Install\Controller;

use App\Install\Entity\Status;
use App\Install\Entity\UserGroup;
use Doctrine\DBAL\Types\TextType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InstallController extends AbstractController
{
    /**
     * True if install process successful
     *
     * @var bool
     */
    private bool $successfullyInstalled = false;

    /**
     * True if install process had an error
     *
     * @var bool
     */
    private bool $errorInstalling = false;

    /**
     * Array of messages during installation process
     *
     * @var array
     */
    private array $messages = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function index(): Response
    {
        if ($this->verifyInstalled()) {
            // Installer is locked, get out!
            return $this->redirectToRoute('app_index');
        }

        if ($this->successfullyInstalled) {
            return $this->render('install/success.html.twig', [
                'messages' => $this->messages,
            ]);
        }

        if ($this->errorInstalling) {
            return $this->render('install/error.html.twig', [
                'messages' => $this->messages,
            ]);
        }

        /**
         * Step 1) Initialize database
         * Step 2) Create admin user
         * Step 3) Create default user group
         * Step 4) Create statuses
         * Step 5) Create tags
         */

        if ($this->successfullyInstalled) {
            $this->lockInstaller();
            return $this->redirectToRoute('app_index');
        }

        return $this->render('install/index.html.twig');
    }

    /**
     * Check if the 'install.lock' file exists
     *
     * @return bool
     */
    public function verifyInstalled(): bool
    {
        $filesystem = new Filesystem();

        $locked = $filesystem->exists(__DIR__ . '/../../../var/install.lock');

        if ($locked !== false) {
            $this->successfullyInstalled = true;
            $this->messages[] = [
                'level' => 'info',
                'message' => 'Installation is already installed',
                'long' => ''
            ];

            return true;
        }

        return false;
    }

    /**
     * Create the 'install.lock' file to lock the installer from running
     *
     * @return void
     */
    public function lockInstaller(): void
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->touch(__DIR__ . '/../../../var/install.lock');
        } catch (IOException $e) {
            $this->successfullyInstalled = false;
            $this->messages[] = [
                'level' => 'error',
                'message' => 'Unable to create lock file',
                'long' => $e->getMessage()
            ];
        }
    }

    /**
     * Creates a new user form
     *
     * @return FormInterface
     */
    private function createUserForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('username', TextType::class, [
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'required' => true,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => ['label' => 'Password', 'hash_property_path' => 'password'],
                'second_options' => ['label' => 'Repeat Password'],
                'mapped' => false,
                'toggle' => true,
            ])
            ->add('firstName', TextType::class, [])
            ->add('lastName', TextType::class, [])
            ->getForm()
        ;
    }

    /**
     * Calls the Status install class to create the default statuses
     *
     * @return bool
     */
    private function setStatus(): bool
    {
        $status = new Status($this->entityManager);
        $status->initialize();

        return $status->verify();
    }

    /**
     * Calls the UserGroup install class to create the default group
     *
     * @return bool
     */
    private function setUserGroup(): bool
    {
        $userGroup = new UserGroup($this->entityManager);
        $userGroup->initialize();

        return $userGroup->verify();
    }
}
