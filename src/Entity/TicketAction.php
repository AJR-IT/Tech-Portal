<?php

namespace App\Entity;

use App\Repository\TicketActionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketActionRepository::class)]
class TicketAction
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $friendlyName = null;

    #[ORM\Column(length: 255)]
    private ?string $fullName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $enabled = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $rolesNeeded = null;

    #[ORM\Column(length: 255)]
    private ?string $triggerAction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFriendlyName(): ?string
    {
        return $this->friendlyName;
    }

    public function setFriendlyName(?string $friendlyName): static
    {
        $this->friendlyName = $friendlyName;

        return $this;
    }

    public function getFullName(): ?string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): static
    {
        $this->fullName = $fullName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getRolesNeeded(): ?array
    {
        return $this->rolesNeeded;
    }

    public function setRolesNeeded(?array $rolesNeeded): static
    {
        $this->rolesNeeded = $rolesNeeded;

        return $this;
    }

    public function getTriggerAction(): ?string
    {
        return $this->triggerAction;
    }

    public function setTriggerAction(string $triggerAction): static
    {
        $this->triggerAction = $triggerAction;

        return $this;
    }
}
