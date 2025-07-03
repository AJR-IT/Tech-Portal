<?php

declare(strict_types=1);


namespace App\Install\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class InstallController extends AbstractController
{
    public function index(): Response
    {
        return $this->render('install/index.html.twig');
    }
}
