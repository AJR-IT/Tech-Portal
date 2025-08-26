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
            ['username' => 'testRequester', 'password' => 'test', 'email' => 'testRequester@local.com'],
            ['username' => 'testTechnician', 'password' => 'test', 'email' => 'testTechnician@local.com'],
        ];

        foreach ($users as $user) {
            $entity = new User();
            $entity->setUsername($user['username']);
            $entity->setPassword($user['password']);
            $entity->setEmail($user['email']);
            $entity->setDateCreated(new \DateTimeImmutable());
            $manager->persist($entity);
            $manager->flush();

            $this->addReference($user['username'], $entity);
        }


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
