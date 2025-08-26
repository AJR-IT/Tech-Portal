<?php

namespace App\DataFixtures;

use App\Entity\Device;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Random\RandomException;

class DeviceSampleFixture extends Fixture implements DependentFixtureInterface
{
    /**
     * @throws \DateMalformedStringException
     * @throws RandomException
     */
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $device = new Device();

            $device
                ->setDecommissioned(false)
                ->setDatePurchased(new \DateTimeImmutable(''.random_int(1, 60).' days'))
                ->setDateCreated(new \DateTimeImmutable('-'.random_int(1, 30).' days'))
                ->setDateModified(new \DateTimeImmutable('-'.random_int(1, 30).' days'))
                ->setDateWarrantyStart(new \DateTimeImmutable('-'.random_int(1, 30).' days'))
                ->setDateWarrantyEnd(new \DateTimeImmutable('+'.random_int(1, 30).' days'))
                ->setAssetTag('ABC'.$i.random_int(1, 100))
                ->setSerialNumber('ZYX'.$i.random_int(1, 100))
            ;

            if ($i % 2) {
                $user = $this->getReference('testRequester', User::class);
            } else {
                $user = $this->getReference('testTechnician', User::class);
            }

            $device->setAssignedTo($user);

            $manager->persist($device);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserSampleFixture::class,
        ];
    }
}
