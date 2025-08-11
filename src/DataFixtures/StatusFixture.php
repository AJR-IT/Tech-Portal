<?php

namespace App\DataFixtures;

use App\Entity\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class StatusFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $statuses = [
            ['fullName' => 'Closed', 'friendlyName' => 'Closed', 'description' => 'Object is closed.'],
            ['fullName' => 'Resolved', 'friendlyName' => 'Resolved', 'description' => 'Object is resolved.'],
            ['fullName' => 'Open', 'friendlyName' => 'Open', 'description' => 'Object is open.'],
            ['fullName' => 'Cancelled', 'friendlyName' => 'Cancelled', 'description' => 'Object is cancelled.'],
            ['fullName' => 'Waiting', 'friendlyName' => 'Waiting', 'description' => 'Object is waiting.'],
        ];

        foreach ($statuses as $status) {
            $entity = new Status();
            $entity->setDescription($status['description']);
            $entity->setDeletable(false);
            $entity->setFriendlyName($status['friendlyName']);
            $entity->setFullName($status['fullName']);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
