<?php

namespace App\Repository;

use App\Entity\Device;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Device>
 */
class DeviceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Device::class);
    }

    public function create(array $deviceData): ?Device
    {
        try {
            $data = [
                'assetTag' => $deviceData['assetTag'] ?? null,
                'serialNumber' => $deviceData['serialNumber'] ?? null,
                'assignedTo' => (array_key_exists('assignedTo', $deviceData) && $deviceData['assignedTo'] instanceof User) ? $deviceData['assignedTo'] : null,
                'decommissioned' => $deviceData['decommissioned'] ?? null, // todo let default behavior be set in config
                'datePurchased' => (array_key_exists('datePurchased', $deviceData) && $deviceData['datePurchased'] instanceof \DateTimeImmutable) ? $deviceData['datePurchased'] : null,
                'dateWarrantyStart' => (array_key_exists('dateWarrantyStart', $deviceData) && $deviceData['dateWarrantyStart'] instanceof \DateTimeImmutable) ? $deviceData['dateWarrantyStart'] : null,
                'dateWarrantyEnd' => (array_key_exists('dateWarrantyEnd', $deviceData) && $deviceData['dateWarrantyEnd'] instanceof \DateTimeImmutable) ? $deviceData['dateWarrantyEnd'] : null,
            ];

            $newDevice = new Device();

            $newDevice
                ->setAssetTag($data['assetTag'])
                ->setDateCreated(new \DateTimeImmutable())
                ->setDateModified(new \DateTimeImmutable())
                ->setAssignedTo($data['assignedTo'])
                ->setDecommissioned($data['decommissioned'])
                ->setDatePurchased($data['datePurchased'])
                ->setDateWarrantyEnd($data['dateWarrantyEnd'])
                ->setDateWarrantyStart($data['dateWarrantyStart'])
                ->setSerialNumber($data['serialNumber'])
            ;

            $this->getEntityManager()->persist($newDevice);

            $this->getEntityManager()->flush();
        } catch (\Exception) {
            return null;
        }

        return $newDevice;
    }

    public function decommissionDevices(array $ids): void
    {
        $this->createQueryBuilder('device')
            ->update('device')
            ->set('device.decommissioned', true)
            ->andWhere('device.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }
}
