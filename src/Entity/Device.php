<?php

namespace App\Entity;

use App\Repository\DeviceRepository;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: DeviceRepository::class)]
class Device
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $assetTag = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serialNumber = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModified = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $datePurchased = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateWarrantyStart = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateWarrantyEnd = null;

    #[ORM\Column(nullable: true)]
    private ?bool $decommissioned = null;

    #[ORM\ManyToOne(inversedBy: 'assignedDevices')]
    private ?User $assignedTo = null;

    /**
     * Generate a random string. Can be used for asset tags, serial numbers, etc.
     *
     * @throws RandomException
     */
    public function generateRandomString(int $length = 7, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'): string
    {
        $keyspace = str_shuffle($keyspace);

        if ($length < 1) {
            throw new \RangeException('Length must be a positive integer');
        }

        $pieces = [];

        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAssetTag(): ?string
    {
        return $this->assetTag;
    }

    public function setAssetTag(?string $assetTag): static
    {
        $this->assetTag = $assetTag;

        return $this;
    }

    public function getSerialNumber(): ?string
    {
        return $this->serialNumber;
    }

    public function setSerialNumber(?string $serialNumber): static
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?\DateTimeImmutable $dateCreated): static
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateModified(): ?\DateTimeImmutable
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeImmutable $dateModified): static
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getDatePurchased(): ?\DateTimeImmutable
    {
        return $this->datePurchased;
    }

    public function setDatePurchased(?\DateTimeImmutable $datePurchased): static
    {
        $this->datePurchased = $datePurchased;

        return $this;
    }

    public function getDateWarrantyStart(): ?\DateTimeImmutable
    {
        return $this->dateWarrantyStart;
    }

    public function setDateWarrantyStart(?\DateTimeImmutable $dateWarrantyStart): static
    {
        $this->dateWarrantyStart = $dateWarrantyStart;

        return $this;
    }

    public function getDateWarrantyEnd(): ?\DateTimeImmutable
    {
        return $this->dateWarrantyEnd;
    }

    public function setDateWarrantyEnd(?\DateTimeImmutable $dateWarrantyEnd): static
    {
        $this->dateWarrantyEnd = $dateWarrantyEnd;

        return $this;
    }

    public function isDecommissioned(): ?bool
    {
        return $this->decommissioned;
    }

    public function setDecommissioned(?bool $decommissioned): static
    {
        $this->decommissioned = $decommissioned;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assignedTo;
    }

    public function setAssignedTo(?User $assignedTo): static
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }
}
