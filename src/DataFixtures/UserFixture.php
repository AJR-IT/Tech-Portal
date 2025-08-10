<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $users = [
           ['username' => 'testRequester', 'password' => 'test', 'email' => 'testRequester@local.com'],
           ['username' => 'testTechnician', 'password' => 'test', 'email' => 'testTechnician@local.com']
        ];

        foreach ($users as $user) {
            $entity = new User();
            $entity->setUsername($user['username']);
            $entity->setPassword($user['password']);
            $entity->setEmail($user['email']);
            $entity->setDateCreated(new DateTimeImmutable());
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
