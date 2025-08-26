<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Random\RandomException;

class UserSampleFixture extends Fixture
{
    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; ++$i) {
            $user = new User();

            $user
                ->setFirstName($faker->firstName())
                ->setLastName($faker->lastName())
                ->setEmail($faker->email())
                ->setPassword($faker->password())
                ->setRoles(['ROLE_USER'])
                ->setDateCreated(new \DateTimeImmutable('-'.random_int(1, 30).' days'))
                ->setUsername($faker->userName())
                ->setEnabled($faker->boolean())
                ->setLocalUniqueId($faker->uuid())
                ->setCanLogIn($faker->boolean())
                ->setDefaultStartingPage('app_device_index')
            ;

            $manager->persist($user);
        }

        $manager->flush();
    }
}
