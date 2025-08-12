<?php

declare(strict_types=1);

namespace App\Install\Entity;

use App\Entity\UserGroup as UserGroupEntity;
use Doctrine\ORM\EntityManagerInterface;

final readonly class UserGroup implements InstallerEntityInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function initialize(): void
    {
        $userGroup = new UserGroupEntity();
        $userGroup->setDateCreated(new \DateTimeImmutable('now'))
            ->setAssignable(true)
            ->setName('Default Group')
            ->setDescription('System defined default group')
        ;

        $this->entityManager->persist($userGroup);
        $this->entityManager->flush();
    }

    public function verify(): bool
    {
        $userGroup = $this->entityManager->getRepository(UserGroupEntity::class)->findOneBy(['name' => 'Default Group']);

        return null !== $userGroup;
    }
}
