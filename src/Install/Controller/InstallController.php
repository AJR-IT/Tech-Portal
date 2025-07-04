<?php

declare(strict_types=1);

namespace App\Install\Controller;

use App\Entity\Config;
use App\Entity\User;
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

    /**
     * Landing page for installation
     *
     * @return Response
     */
    #[Route('/install', name: 'install_index')]
    public function index(Request $request): Response
    {
        // If install.lock exists, exit installer
        if ($this->verifyInstalled()) {
            return $this->redirectToRoute('app_index');
        }

        $installForm = $this->setUpInstallationForm();
        $installForm->handleRequest($request);

        if ($installForm->isSubmitted() && $installForm->isValid()) {
            $data = $installForm->getData();

            $users = $this->entityManager->getRepository(User::class)->findAll();

            if (count($users) > 0) {
                foreach ($users as $user) {
                    $this->entityManager->remove($user);
                }

                $this->entityManager->flush();
            }

            // Set up user
            $user = new User();
            $user->setEmail($data['email'])
                ->setUsername($data['username'])
                ->setPassword($this->passwordHasher->hashPassword($user, $data['plainPassword']))
                ->setCanLogIn(true)
                ->setFirstName($data['firstName'])
                ->setLastName($data['lastName'])
            ;

            $this->entityManager->persist($user);

            // Set up config
            foreach (['siteName' => $data['siteName'], 'siteUrl' => $data['siteUrl']] as $key => $val) {
                $config = new Config();
                $config->setConfigKey($key)
                    ->setConfigValue($val)
                ;
            }

            $this->entityManager->persist($config);
            $this->entityManager->flush();

            $this->setUserGroup();
            $this->setStatus();
            $this->setTag();

            if (!$this->errorInstalling) {
                $this->lockInstaller();
                return $this->redirectToRoute('install_success');
            }

            return $this->render('install/index.html.twig', [
                'form' => $installForm->createView(),
                'data' => $data,
                'messages' => $this->messages,
            ]);
        }

        return $this->render('install/welcome.html.twig', [
            'form' => $installForm->createView(),
            'messages' => $this->messages,
        ]);
    }

    #[Route('/install/success', name: 'install_success')]
    public function installSuccess(): Response
    {
        return $this->render('install/success.html.twig');
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

        if ($locked === false) {
            return false;
        }

        $this->successfullyInstalled = true;
        $this->messages[] = [
            'level' => 'info',
            'message' => 'Installation is already installed',
            'fullMessage' => ''
        ];

        return true;
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
                'level' => 'danger',
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
    private function setUpInstallationForm(): FormInterface
    {
        return $this->createFormBuilder()
            // App name & url
            ->add('siteName', TextType::class)
            ->add('siteUrl', TextType::class)

            // Admin Account
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
            $this->messages[] = ['level' => 'danger', 'message' => 'Failed to create statuses', 'fullMessage' => ''];
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
            $this->messages[] = ['level' => 'danger', 'message' => 'Failed to create tags', 'fullMessage' => ''];
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
            $this->messages[] = ['level' => 'danger', 'message' => 'Failed to create user groups', 'fullMessage' => ''];
        }
    }
}
