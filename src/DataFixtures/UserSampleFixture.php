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
        $users = [
            ['username' => 'testRequester', 'password' => 'test', 'email' => 'testRequester@local.com', 'role' => 'ROLE_USER'],
            ['username' => 'testTechnician', 'password' => 'test', 'email' => 'testTechnician@local.com', 'role' => 'ROLE_TECHNICIAN'],
        ];

        foreach ($users as $user) {
            $entity = new User();

            $entity
                ->setUsername($user['username'])
                ->setPassword($user['password'])
                ->setEmail($user['email'])
                ->setDateCreated(new \DateTimeImmutable())
                ->setRoles([$user['role']])
            ;

            $manager->persist($entity);

            $manager->flush();

            $this->addReference($user['username'], $entity);
        }

        $faker = Factory::create('en-US');

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
