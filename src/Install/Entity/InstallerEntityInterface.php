<?php

declare(strict_types=1);


namespace App\Install\Entity;

interface InstallerEntityInterface
{
    /**
     * Initialize entity data
     *
     * @return void
     */
    public function initialize(): void;

    public function verify(): bool;
}
