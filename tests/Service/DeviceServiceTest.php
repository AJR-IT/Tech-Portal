<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\DeviceService;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DeviceServiceTest extends WebTestCase
{
    protected function setUp(): void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        // (3) run some service & test the result
        $deviceService = $container->get(DeviceService::class);
    }

    public function testTrue(): void
    {
        $this->assertTrue(true);
    }
}
