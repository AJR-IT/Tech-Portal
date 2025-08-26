<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Device;
use App\Entity\User;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeviceServiceTest extends WebTestCase
{
    protected DeviceRepository $deviceRepository;

    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        $this->deviceRepository = $container->get(DeviceRepository::class);

        $this->entityManager = $container->get('doctrine')->getManager();
    }

    public function testCreate(): void
    {
        $device = $this->deviceRepository->create([
            'assetTag' => 'ABD485',
            'serialNumber' => 'AHGAHG7GAHG',
        ]);

        $this->assertInstanceOf(Device::class, $device);

        $this->assertEquals('ABD485', $device->getAssetTag());

        $this->assertEquals('AHGAHG7GAHG', $device->getSerialNumber());
    }

    public function testUpdate(): void
    {
        $device = $this->deviceRepository->create([
            'assetTag' => 'ABD485',
            'serialNumber' => 'AHGAHG7GAHG',
        ]);

        $device->setAssetTag('ABD4851234');

        $this->entityManager->flush();

        $this->assertEquals('ABD4851234', $device->getAssetTag());

        $this->assertEquals('AHGAHG7GAHG', $device->getSerialNumber());
    }

    public function testDelete(): void
    {
        $device = $this->deviceRepository->create([
            'assetTag' => 'ABD485',
        ]);

        $deviceId = $device->getId();

        $this->assertInstanceOf(Device::class, $device);

        $this->entityManager->remove($device);

        $this->entityManager->flush();

        $this->assertNull($this->deviceRepository->find($deviceId));
    }

    public function testDecommissionedDefault(): void
    {
        $device = $this->deviceRepository->create([
            'assetTag' => 'ABD485',
        ]);

        $this->assertNotTrue($device->isDecommissioned());
    }

    public function testAssignedUser(): void
    {
        $user = new User();

        $user
            ->setUsername('test')
            ->setPassword('test')
            ->setEmail('test@test.local')
            ->setDateCreated(new \DateTimeImmutable())
        ;

        $this->entityManager->persist($user);

        $this->entityManager->flush();

        $device = $this->deviceRepository->create([
            'assetTag' => 'ABD485',
            'serialNumber' => 'AHGAHG7GAHG',
            'assignedTo' => $user,
        ]);

        $this->assertEquals($user, $device->getAssignedTo());
    }

//    public function testWarrantyDateValidation(): void
//    {
//        $warrantyStartDate = new \DateTimeImmutable();
//        $warrantyEndDate = new \DateTimeImmutable('now - 30 days');
//
//        $device = $this->deviceRepository->create([
//            'dateWarrantyStart' => $warrantyStartDate,
//            'dateWarrantyEnd' => $warrantyEndDate,
//        ]);
//
//        $this->assertGreaterThan($device->getDateWarrantyStart(), $device->getDateWarrantyEnd());
//    }
}
