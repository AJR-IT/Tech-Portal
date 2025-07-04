<?php

namespace App\Entity;

use App\Repository\ConfigRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConfigRepository::class)]
class Config
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $config_key = null;

    #[ORM\Column(length: 255)]
    private ?string $config_value = null;

    #[ORM\Column(length: 255)]
    private ?string $default_value = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getConfigKey(): ?string
    {
        return $this->config_key;
    }

    public function setConfigKey(string $config_key): static
    {
        $this->config_key = $config_key;

        return $this;
    }

    public function getConfigValue(): ?string
    {
        return $this->config_value;
    }

    public function setConfigValue(string $config_value): static
    {
        $this->config_value = $config_value;

        return $this;
    }

    public function getDefaultValue(): ?string
    {
        return $this->default_value;
    }

    public function setDefaultValue(string $default_value): static
    {
        $this->default_value = $default_value;

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
}
