<?php

namespace App\Tests\Install\Controller;

use App\Install\Controller\InstallController;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class InstallControllerTest extends KernelTestCase
{
    public function testLockInstaller(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        $installController = $container->get(InstallController::class);

        $installController->lockInstaller();

        $this->assertFileExists(__DIR__ . '/../../../var/install.lock');

        $this->removeLockFile();
    }

    public function testVerifyInstalled()
    {
        self::bootKernel();
        $container = self::getContainer();

        $installController = $container->get(InstallController::class);

        $locked = $installController->verifyInstalled();

        $this->assertFalse($locked);

        $installController->lockInstaller();

        $locked = $installController->verifyInstalled();

        $this->assertTrue($locked);

        $this->removeLockFile();
    }

    private function removeLockFile()
    {
        $filesystem = new Filesystem();
        if ($filesystem->exists(__DIR__ . '/../../../var/install.lock')) {
            $filesystem->remove(__DIR__ . '/../../../var/install.lock');
        }
    }
}
