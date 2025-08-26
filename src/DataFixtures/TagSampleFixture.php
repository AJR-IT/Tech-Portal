<?php

namespace App\DataFixtures;

use App\Entity\Tag;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TagSampleFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tags = [
            ['friendlyName' => 'Open', 'fullName' => 'Open', 'description' => 'Object is open and waiting.'],
            ['friendlyName' => 'Closed', 'fullName' => 'Closed', 'description' => 'Object is closed.'],
            ['friendlyName' => 'Resolved', 'fullName' => 'Resolved', 'description' => 'Object is resolved.'],
            ['friendlyName' => 'Pending Approval', 'fullName' => 'Pending Approval', 'description' => 'Object is pending approval from management.'],
            ['friendlyName' => 'Waiting On Parts', 'fullName' => 'Waiting On Parts', 'description' => 'Object is waiting on parts.'],
            ['friendlyName' => 'Waiting On Requester', 'fullName' => 'Waiting On Requester', 'description' => 'Object is waiting on response from requester.'],
        ];

        foreach ($tags as $tag) {
            $entity = new Tag();

            $entity
                ->setFullName($tag['fullName'])
                ->setFriendlyName($tag['friendlyName'])
                ->setDescription($tag['description'])
                ->setDeletable(false)
            ;

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
