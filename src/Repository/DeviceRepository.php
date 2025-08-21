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

    /**
     * @param array $deviceData
     * @return Device|null
     */
    public function createDevices(array $deviceData): ?Device
    {
        $data = [
            'assetTag' => $deviceData['assetTag'] ?? null,
            'serialNumber' => $deviceData['serialNumber'] ?? null,
            'assignedTo' => ($deviceData['assignedTo'] instanceof User) ? $deviceData['assignedTo'] : null,
            'decommissioned' => $deviceData['decommissioned'] ?? null, // todo let default behavior be set in config
            'datePurchased' => ($deviceData['datePurchased'] instanceof \DateTimeImmutable) ? $deviceData['datePurchased'] : null,
            'dateWarrantyStart' => ($deviceData['dateWarrantyStart'] instanceof \DateTimeImmutable) ? $deviceData['dateWarrantyStart'] : null,
            'dateWarrantyEnd' => ($deviceData['dateWarrantyEnd'] instanceof \DateTimeImmutable) ? $deviceData['dateWarrantyEnd'] : null,
        ];

        $device = new Device();

        $device
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

        $this->getEntityManager()->persist($device);

        $this->getEntityManager()->flush();

        return $device;
    }

    /**
     * @param array $deviceIds
     * @return array
     */
    public function findById(array $deviceIds): array
    {
        return $this->createQueryBuilder('device')
            ->andWhere('device.id IN (:deviceIds)')
            ->setParameter('deviceIds', $deviceIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array $filter
     * @return array
     */
    public function getDevices(array $filter = []): array
    {
        return $this->findBy($filter);
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
