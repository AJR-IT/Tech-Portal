<?php

namespace App\Controller;

use App\Repository\DeviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/device', name: 'app_device_')]
final class DeviceController extends AbstractController
{
    #[Route('/{id?}', name: 'show')]
    public function index(DeviceRepository $deviceRepository, ?int $id = null): Response
    {
        if (null === $id) {
            return $this->render('device/index.html.twig', [
                'devices' => $deviceRepository->findAll(),
            ]);
        }

        return $this->render('device/show.html.twig', [
            'device' => $deviceRepository->find($id),
        ]);
    }
}
