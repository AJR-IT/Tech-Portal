<?php

declare(strict_types=1);

namespace App\Install;

enum InstallStatus: int
{
    case ALREADY_INSTALLED = 1;
    case ERROR_INSTALLING = 2;
    case NOT_INSTALLED = 3;
    case SUCCESSFUL_INSTALL = 0;
}
