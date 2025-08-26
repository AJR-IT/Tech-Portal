<?php

namespace App\DataFixtures;

use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\UserGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;

class TicketSampleFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('en-US');

        $requester = $this->getReference('testRequester', User::class);
        $technician = $this->getReference('testTechnician', User::class);
        $technicianGroup = $this->getReference('technicianGroup', UserGroup::class);
        $openStatus = $this->getReference('status-open', Status::class);

        for ($i = 0; $i < 50; ++$i) {
            $ticket = new Ticket();

            $ticket
                ->setDateCreated(new \DateTimeImmutable('-'.random_int(0, 30).' days'))
                ->setDateModified(null)
                ->setDateDue(new \DateTimeImmutable('+'.random_int(0, 30).' days'))
                ->setStatus($openStatus)
                ->setRequestingUser($requester)
                ->setOriginalMessage($faker->text())
                ->setSubject(sprintf('Ticket #%d', $i))
            ;

            if ($i % 2) {
                $ticket->setAssignedUser($technician);
            }

            if ($i % 9) {
                $ticket->setAssignedGroup($technicianGroup);
            }

            $manager->persist($ticket);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserSampleFixture::class,
            UserGroupSampleFixture::class,
            StatusSampleFixture::class,
        ];
    }
}
