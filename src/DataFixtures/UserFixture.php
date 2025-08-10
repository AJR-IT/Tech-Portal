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
        $user = new User();
        $user->setUsername('test');
        $user->setPassword('test');
        $user->setEmail('test@local.com');
        $user->setDateCreated(new DateTimeImmutable());
        $manager->persist($user);

        $manager->flush();
    }
}
