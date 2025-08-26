<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserGroupSampleFixture extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $userTechnician = $this->getReference('testTechnician', User::class);
        $userRequester = $this->getReference('testRequester', User::class);

        $technicianGroup = new UserGroup();

        $technicianGroup
            ->setName('Technicians')
            ->setDateCreated(new \DateTimeImmutable())
            ->setAssignable(true)
            ->setDescription('Group of technicians')
            ->addMember($userTechnician)
        ;

        $this->addReference('technicianGroup', $technicianGroup);

        $manager->persist($technicianGroup);

        $userGroup = new UserGroup();

        $userGroup
            ->setName('Users')
            ->setDateCreated(new \DateTimeImmutable())
            ->setAssignable(true)
            ->setDescription('Group of users')
            ->addMember($userRequester)
        ;

        $manager->persist($userGroup);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserSampleFixture::class,
        ];
    }
}
