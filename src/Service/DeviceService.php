<?php

namespace App\Service;

use App\Entity\Device;
use App\Repository\DeviceRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DeviceService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DeviceRepository       $deviceRepository)
    {
    }

    public function create(array $data): ?Device
    {
        try {
            $device = new Device();

            $device
                ->setAssetTag($data['assetTag'] ?? null)
                ->setSerialNumber($data['serialNumber'] ?? null)
                ->setDateCreated(new \DateTimeImmutable())
                ->setDateModified(new \DateTimeImmutable())
                ->setAssignedTo($data['assignedTo'] ?? null)
                ->setDatePurchased($data['datePurchased'] ?? null)
                ->setDateWarrantyStart($data['dateWarrantyStart'] ?? null)
                ->setDateWarrantyEnd($data['dateWarrantyEnd'] ?? null)
                ->setDecommissioned($data['decommissioned'] ?? null)
            ;

            $this->entityManager->persist($device);

            $this->entityManager->flush();
        } catch (\Exception) {
            return null;
        }

        return $device;
    }

    public function getDeviceById(int $id): ?Device
    {
        return $this->deviceRepository->find($id);
    }
}
