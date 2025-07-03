<?php

declare(strict_types=1);

namespace App\Install\Controller;

use App\Install\Entity\Status;
use App\Install\Entity\Tag;
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
use Symfony\Component\Routing\Attribute\Route;

#[Route('/install', name: 'install_')]
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
//        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        if ($this->verifyInstalled()) {
            throw new \RuntimeException('Illegal action, installation already succeeded.');
        }
    }

    /**
     * Landing page for installation
     *
     * @return Response
     */
    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        if ($this->errorInstalling) {
            return $this->failInstallation();
        }

        return $this->render('install/index.html.twig', [
            'messages' => $this->messages,
        ]);
    }

    #[Route('/user-setup', name: 'user_setup')]
    public function stepUserSetup(Request $request): Response
    {
        if ($this->errorInstalling) {
            return $this->failInstallation();
        }

        return $this->render('install/user_setup.html.twig', [
            'form' => $this->createUserForm()->createView(),
            'messages' => $this->messages,
        ]);
    }

    #[Route('/user-group-setup', name: 'user_group_setup')]
    public function stepUserGroupSetup(Request $request): Response
    {
        $this->setUserGroup();

        if ($this->errorInstalling) {
            return $this->failInstallation();
        }

        return $this->render('install/user_group_setup.html.twig', [
            'messages' => $this->messages,
        ]);
    }

    #[Route('/status-setup', name: 'status_setup')]
    public function stepStatusSetup(Request $request): Response
    {
        $this->setStatus();

        if ($this->errorInstalling) {
            return $this->failInstallation();
        }

        return $this->render('install/status_setup.html.twig', [
            'messages' => $this->messages,
        ]);
    }

    #[Route('/tags-setup', name: 'tags_setup')]
    public function stepTagsSetup(Request $request): Response
    {
        $this->setTag();

        if ($this->errorInstalling) {
            return $this->failInstallation();
        }

        return $this->render('install/tags_setup.html.twig', [
            'messages' => $this->messages,
        ]);
    }

    public function stepFinal(Request $request): Response
    {
        $tag = new Tag($this->entityManager);
        $status = new Status($this->entityManager);
        $userGroup = new UserGroup($this->entityManager);

        if (
            !$tag->verify() ||
            !$status->verify() ||
            !$userGroup->verify() ||
            !$this->errorInstalling
        ) {
            return $this->failInstallation();
        }

        // All checks passed
        return $this->render('install/final_setup.html.twig', [
            'messages' => $this->messages,
        ]);
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
                'fullMessage' => ''
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
                'fullMessage' => $e->getMessage()
            ];
        }
    }

    private function failInstallation(): Response
    {
        return $this->render('install/error.html.twig', [
            'messages' => $this->messages,
        ]);
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
            ->add('firstName', TextType::class)
            ->add('lastName', TextType::class)
            ->getForm()
        ;
    }

    /**
     * Calls the Status install class to create the default statuses
     *
     * @return void
     */
    private function setStatus(): void
    {
        $status = new Status($this->entityManager);
        $status->initialize();

        if ($status->verify() === false) {
            $this->errorInstalling = true;
            $this->messages[] = ['level' => 'error', 'message' => 'Failed to create statuses', 'fullMessage' => ''];
        }
    }

    /**
     * Calls the Tag install class to create the default tags
     *
     * @return void
     */
    private function setTag(): void
    {
        $tag = new Tag($this->entityManager);
        $tag->initialize();

        if ($tag->verify() === false) {
            $this->errorInstalling = true;
            $this->messages[] = ['level' => 'error', 'message' => 'Failed to create tags', 'fullMessage' => ''];
        }
    }

    /**
     * Calls the UserGroup install class to create the default group
     *
     * @return void
     */
    private function setUserGroup(): void
    {
        $userGroup = new UserGroup($this->entityManager);
        $userGroup->initialize();

        if ($userGroup->verify() === false) {
            $this->errorInstalling = true;
            $this->messages[] = ['level' => 'error', 'message' => 'Failed to create user groups', 'fullMessage' => ''];
        }
    }
}
