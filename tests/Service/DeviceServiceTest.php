<?php

namespace App\Tests\Service;

use App\Entity\Device;
use App\Service\DeviceService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeviceServiceTest extends KernelTestCase
{
    private DeviceService $deviceService;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        //        $this->entityManager = $container->get('doctrine')->getManager();

        $this->deviceService = $container->get(DeviceService::class);

        parent::setUp();
    }

    public function testCreate(): void
    {
        $data = [
            'assetTag' => 1007812,
            'serialNumber' => 'JAHANGIR7',
        ];

        $device = $this->deviceService->create($data);

//        $this->assertInstanceOf(Device::class, $device);
    }
}
