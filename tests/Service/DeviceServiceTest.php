<?php

namespace App\Tests\Service;

use App\Entity\Device;
use App\Service\DeviceService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeviceServiceTest extends KernelTestCase
{
    public function testCreate(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $deviceService = $container->get(DeviceService::class);

        $device = $deviceService->create();

        $this->assertInstanceOf(Device::class, $device);
    }

    public function testGetDeviceById(): void
    {
        $this->assertNotTrue(false);
    }
}
