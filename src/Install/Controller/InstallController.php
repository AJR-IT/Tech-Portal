<?php

declare(strict_types=1);

namespace App\Install\Controller;

use App\Entity\Config;
use App\Entity\Status as StatusEntity;
use App\Entity\Tag as TagEntity;
use App\Entity\User as UserEntity;
use App\Entity\UserGroup as UserGroupEntity;
use App\Install\Entity\Status;
use App\Install\Entity\Tag;
use App\Install\Entity\TicketAction;
use App\Install\Entity\UserGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class InstallController extends AbstractController
{
    /**
     * True if an installation process had an error.
     */
    private bool $errorInstalling = false;

    /**
     * Array of messages during an installation process.
     */
    private array $messages = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    /**
     * Landing page for installation.
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

            $this->clearOldData();

            // Set up user
            $user = new UserEntity();
            $user->setEmail($data['email'])
                ->setUsername($data['username'])
                ->setPassword($this->passwordHasher->hashPassword($user, $data['plainPassword']))
                ->setCanLogIn(true)
                ->setDateCreated(new \DateTimeImmutable('now'))
                ->setFirstName($data['firstName'])
                ->setLastName($data['lastName'])
                ->setRoles(['ROLE_ADMIN'])
            ;

            $this->entityManager->persist($user);

            // Set up config
            foreach (['siteName' => $data['siteName'], 'siteUrl' => $data['siteUrl']] as $key => $val) {
                $config = new Config();
                $config->setConfigKey($key)
                    ->setConfigValue($val)
                    ->setDefaultValue('')
                ;
            }

            $this->entityManager->persist($config);
            $this->entityManager->flush();

            $this->setUserGroup();
            $this->setStatus();
            $this->setTag();
            $this->setTicketActions();

            return (!$this->errorInstalling) ? $this->installSuccess() : $this->installFail();
        }

        return $this->render('install/index.html.twig', [
            'form' => $installForm->createView(),
            'messages' => $this->messages,
        ]);
    }

    private function installFail(): Response
    {
        $this->addFlash('error', 'Installation failed.');

        return $this->render('install/finish_install.html.twig', [
            'success' => false,
            'messages' => $this->messages,
        ]);
    }

    private function installSuccess(): Response
    {
        $this->lockInstaller();

        return $this->render('install/finish_install.html.twig', [
            'success' => true,
            'messages' => $this->messages,
        ]);
    }

    /**
     * Check if the 'install.lock' file exists.
     */
    public function verifyInstalled(): bool
    {
        $filesystem = new Filesystem();

        $locked = $filesystem->exists(__DIR__.'/../../../var/install.lock');

        if (false === $locked) {
            return false;
        }

        $this->errorInstalling = false;

        $this->messages[] = [
            'level' => 'info',
            'message' => 'Installation is already installed',
            'fullMessage' => '',
        ];

        return true;
    }

    /**
     * Create the 'install.lock' file to lock the installer from running.
     */
    public function lockInstaller(): void
    {
        $filesystem = new Filesystem();

        try {
            $filesystem->touch(__DIR__.'/../../../var/install.lock');
        } catch (IOException $e) {
            $this->errorInstalling = true;

            $this->messages[] = [
                'level' => 'danger',
                'message' => 'Unable to create lock file',
                'fullMessage' => $e->getMessage(),
            ];
        }
    }

    /**
     * Creates the installation form.
     */
    private function setUpInstallationForm(): FormInterface
    {
        return $this->createFormBuilder()
            // App name & url
            ->add('siteName', TextType::class, [
                'label' => 'Site Name',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('siteUrl', TextType::class, [
                'label' => 'Site URL',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])

            // Admin Account
            ->add('username', TextType::class, [
                'label' => 'Username',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                    'attr' => [
                        'minlength' => 6,
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'form-label',
                    ],
                ],
                'second_options' => [
                    'label' => 'Repeat Password',
                    'attr' => [
                        'class' => 'form-control',
                    ],
                    'label_attr' => [
                        'class' => 'form-label',
                    ],
                ],
            ])
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                ],
                'label_attr' => [
                    'class' => 'form-label',
                ],
            ])
            ->add('install', SubmitType::class, [
                'label' => 'Install',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
            ->getForm()
        ;
    }

    /**
     * Calls the Status install class to create the default statuses.
     */
    private function setStatus(): void
    {
        $status = new Status($this->entityManager);
        $status->initialize();

        if (false === $status->verify()) {
            $this->errorInstalling = true;

            $this->messages[] = [
                'level' => 'danger',
                'message' => 'Failed to create statuses',
                'fullMessage' => '',
            ];
        }
    }

    /**
     * Calls the Tag install class to create the default tags.
     */
    private function setTag(): void
    {
        $tag = new Tag($this->entityManager);
        $tag->initialize();

        if (false === $tag->verify()) {
            $this->errorInstalling = true;

            $this->messages[] = [
                'level' => 'danger',
                'message' => 'Failed to create tags',
                'fullMessage' => '',
            ];
        }
    }

    private function setTicketActions(): void
    {
        $action = new TicketAction($this->entityManager);
        $action->initialize();

        if (false === $action->verify()) {
            $this->errorInstalling = true;

            $this->messages[] = [
                'level' => 'danger',
                'message' => 'Failed to create ticket actions',
                'fullMessage' => '',
            ];
        }
    }

    /**
     * Calls the UserGroup install class to create the default group.
     */
    private function setUserGroup(): void
    {
        $userGroup = new UserGroup($this->entityManager);
        $userGroup->initialize();

        if (false === $userGroup->verify()) {
            $this->errorInstalling = true;

            $this->messages[] = [
                'level' => 'danger',
                'message' => 'Failed to create user groups',
                'fullMessage' => '',
            ];
        }
    }

    private function clearOldData(): void
    {
        $users = $this->entityManager->getRepository(UserEntity::class)->findAll();
        $userGroups = $this->entityManager->getRepository(UserGroupEntity::class)->findAll();
        $tags = $this->entityManager->getRepository(TagEntity::class)->findAll();
        $statuses = $this->entityManager->getRepository(StatusEntity::class)->findAll();

        if (count($users) > 0) {
            foreach ($users as $user) {
                $this->entityManager->remove($user);
            }
        }

        if (count($userGroups) > 0) {
            foreach ($userGroups as $userGroup) {
                $this->entityManager->remove($userGroup);
            }
        }

        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $this->entityManager->remove($tag);
            }
        }

        if (count($statuses) > 0) {
            foreach ($statuses as $status) {
                $this->entityManager->remove($status);
            }
        }

        $this->entityManager->flush();
    }
}
