<?php

namespace App\Tests\Service;

use App\Entity\Device;
use App\Service\DeviceService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeviceServiceTest extends KernelTestCase
{
    private DeviceService $deviceService;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->deviceService = $container->get(DeviceService::class);

        $this->entityManager = $container->get('doctrine')->getManager();

        parent::setUp();
    }

    public function testCreate(): void
    {
        $data = [
            'assetTag' => 1007812,
            'serialNumber' => 'JAHANGIR7',
        ];

        $device = $this->deviceService->create($data);

        $this->assertInstanceOf(Device::class, $device);
    }

    public function testCreateExpectException(): void
    {
        $data = [
            'assetTag' => 1007812,
            'datePurchased' => '2024-04-12',
        ];

        $this->expectException(\TypeError::class);

        $device = $this->deviceService->create($data);
    }

    public function testGetDevice(): void
    {
        $device = new Device();

        $date = new \DateTimeImmutable();

        $device->setDateCreated($date);

        $this->entityManager->persist($device);

        $this->entityManager->flush();

        $this->assertInstanceOf(Device::class, $this->deviceService->getDeviceById($device->getId()));
    }
}
