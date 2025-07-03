<?php

declare(strict_types=1);

namespace App\Install\Entity;

use App\Entity\Tag as TagEntity;
use Doctrine\ORM\EntityManagerInterface;

final class Tag implements InstallerEntityInterface
{
    private array $tags = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function initialize(): void
    {
        $this->tags = [
            ['friendlyName' => 'Important', 'fullName' => 'Important', 'description' => 'Important'],
            ['friendlyName' => 'Critical', 'fullName' => 'Critical', 'description' => 'Critical'],
            ['friendlyName' => 'Urgent', 'fullName' => 'Urgent', 'description' => 'Urgent'],
            ['friendlyName' => 'Low', 'fullName' => 'Low Priority', 'description' => 'Low Priority'],
            ['friendlyName' => 'Medium', 'fullName' => 'Medium Priority', 'description' => 'Medium Priority'],
            ['friendlyName' => 'High', 'fullName' => 'High Priority', 'description' => 'High Priority'],
        ];

        foreach ($this->tags as $tag) {
            $tagsEntity = new TagEntity();
            $tagsEntity->setDescription($tag['description'])
                ->setDeletable(false)
                ->setFullName($tag['fullName'])
                ->setFriendlyName($tag['friendlyName'])
            ;

            $this->entityManager->persist($tagsEntity);
        }

        $this->entityManager->flush();
    }

    public function verify(): bool
    {
        $tags = $this->entityManager->getRepository(TagEntity::class)->findAll();

        $tagArray = [];
        $errorCount = 0;

        foreach ($this->tags as $tag) {
            $tagArray[] = $tag['fullName'];
        }

        foreach ($tags as $tag) {
            if (!in_array($tag['fullName'], $tagArray)) {
                $errorCount++;
            }
        }

        return $errorCount === 0;
    }
}
