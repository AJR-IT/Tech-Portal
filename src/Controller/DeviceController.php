<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeviceController extends AbstractController
{
    #[Route('/device', name: 'app_device')]
    public function index(): Response
    {
        return $this->render('device/index.html.twig', [
            'controller_name' => 'DeviceController',
        ]);
    }
}
