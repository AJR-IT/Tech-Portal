<?php

declare(strict_types=1);


namespace App\Install\Entity;

use App\Entity\Status as StatusEntity;
use Doctrine\ORM\EntityManagerInterface;

final class Status implements InstallerEntityInterface
{
    private array $statuses = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initialize(): void
    {
        $this->statuses = [
            ['fullName' => 'Open', 'friendlyName' => 'Open', 'description' => 'Open and no actions performed', 'deletable' => false],
            ['fullName' => 'In Progress', 'friendlyName' => 'In Progress', 'description' => 'Open and currently being attended to', 'deletable' => false],
            ['fullName' => 'On Hold', 'friendlyName' => 'On Hold', 'description' => 'In progress but not currently being attended to', 'deletable' => false],
            ['fullName' => 'Closed', 'friendlyName' => 'Closed', 'description' => 'Closed and no actions performed', 'deletable' => false],
            ['fullName' => 'Resolved', 'friendlyName' => 'Resolved', 'description' => 'Closed with a resolution', 'deletable' => false],
        ];

        foreach ($this->statuses as $s) {
            $status = new StatusEntity();
            $status->setFullName($s['fullName'])
                ->setFriendlyName($s['friendlyName'])
                ->setDescription($s['description'])
                ->setDeletable($s['deletable'])
            ;

            $this->entityManager->persist($status);
        }

        $this->entityManager->flush();
    }

    public function verify(): bool
    {
        $statuses = $this->entityManager->getRepository(StatusEntity::class)->findAll();

        $checkNames = [];
        $errorCount = 0;

        foreach ($this->statuses as $s) {
            $checkNames[] = $s['fullName'];
        }

        foreach ($statuses as $status) {
            if (!in_array($status->getFullName(), $checkNames)) {
                $errorCount++;
            }
        }

        return $errorCount === 0;
    }
}
