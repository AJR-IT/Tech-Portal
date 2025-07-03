<?php

declare(strict_types=1);

namespace App\Install\Entity;

use App\Entity\User as UserEntity;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class User implements InstallerEntityInterface
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface      $entityManager,
    ) {
    }

    public function initialize(): void
    {
        $user = new UserEntity();
        $user->setDateCreated(new DateTimeImmutable('now'))
            ->setEmail('defaultadmin@localhost')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($user, 'changeme'))
            ->setUsername('defaultadmin')
        ;

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function verify(): bool
    {
        $user = $this->entityManager->getRepository(UserEntity::class)->findOneBy(['username' => 'defaultadmin']);
        return $user !== null;
    }
}
