<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var ?string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'requestingUser')]
    private Collection $ticketsSubmitted;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'assignedUser')]
    private Collection $assignedTickets;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'createdByUser')]
    private Collection $createdComments;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'user')]
    private Collection $comments;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stateUniqueId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $localUniqueId = null;

    #[ORM\Column(nullable: true)]
    private ?int $graduationYear = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    /**
     * @var Collection<int, UserGroup>
     */
    #[ORM\ManyToMany(targetEntity: UserGroup::class, mappedBy: 'members')]
    private Collection $userGroups;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'resolvedBy')]
    private Collection $resolvedTickets;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'closedBy')]
    private Collection $closedTickets;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lastName = null;

    #[ORM\Column]
    private bool $enabled = true;

    #[ORM\Column]
    private bool $canLogIn = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $defaultStartingPage = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $lastLogonDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $lsatLogonIp = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ticketDateFormat = null;

    /**
     * @var Collection<int, Device>
     */
    #[ORM\OneToMany(targetEntity: Device::class, mappedBy: 'assignedTo')]
    private Collection $assignedDevices;

    public function __construct()
    {
        $this->ticketsSubmitted = new ArrayCollection();
        $this->assignedTickets = new ArrayCollection();
        $this->createdComments = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->userGroups = new ArrayCollection();
        $this->resolvedTickets = new ArrayCollection();
        $this->closedTickets = new ArrayCollection();
        $this->assignedDevices = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTicketsSubmitted(): Collection
    {
        return $this->ticketsSubmitted;
    }

    public function addTicketsSubmitted(Ticket $ticketsSubmitted): static
    {
        if (!$this->ticketsSubmitted->contains($ticketsSubmitted)) {
            $this->ticketsSubmitted->add($ticketsSubmitted);
            $ticketsSubmitted->setRequestingUser($this);
        }

        return $this;
    }

    public function removeTicketsSubmitted(Ticket $ticketsSubmitted): static
    {
        if ($this->ticketsSubmitted->removeElement($ticketsSubmitted)) {
            // set the owning side to null (unless already changed)
            if ($ticketsSubmitted->getRequestingUser() === $this) {
                $ticketsSubmitted->setRequestingUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getAssignedTickets(): Collection
    {
        return $this->assignedTickets;
    }

    public function addAssignedTicket(Ticket $assignedTicket): static
    {
        if (!$this->assignedTickets->contains($assignedTicket)) {
            $this->assignedTickets->add($assignedTicket);
            $assignedTicket->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedTicket(Ticket $assignedTicket): static
    {
        if ($this->assignedTickets->removeElement($assignedTicket)) {
            // set the owning side to null (unless already changed)
            if ($assignedTicket->getAssignedUser() === $this) {
                $assignedTicket->setAssignedUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getCreatedComments(): Collection
    {
        return $this->createdComments;
    }

    public function addCreatedComment(Comment $comment): static
    {
        if (!$this->createdComments->contains($comment)) {
            $this->createdComments->add($comment);
            $comment->setCreatedByUser($this);
        }

        return $this;
    }

    public function removeCreatedComment(Comment $comment): static
    {
        if ($this->createdComments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getCreatedByUser() === $this) {
                $comment->setCreatedByUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Comment>
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): static
    {
        if (!$this->comments->contains($comment)) {
            $this->comments->add($comment);
            $comment->setUser($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getUser() === $this) {
                $comment->setUser(null);
            }
        }

        return $this;
    }

    public function getStateUniqueId(): ?string
    {
        return $this->stateUniqueId;
    }

    public function setStateUniqueId(?string $stateUniqueId): static
    {
        $this->stateUniqueId = $stateUniqueId;

        return $this;
    }

    public function getLocalUniqueId(): ?string
    {
        return $this->localUniqueId;
    }

    public function setLocalUniqueId(?string $localUniqueId): static
    {
        $this->localUniqueId = $localUniqueId;

        return $this;
    }

    public function getGraduationYear(): ?int
    {
        return $this->graduationYear;
    }

    public function setGraduationYear(?int $graduationYear): static
    {
        $this->graduationYear = $graduationYear;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeImmutable
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeImmutable $dateCreated): static
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, UserGroup>
     */
    public function getUserGroups(): Collection
    {
        return $this->userGroups;
    }

    public function addUserGroup(UserGroup $userGroup): static
    {
        if (!$this->userGroups->contains($userGroup)) {
            $this->userGroups->add($userGroup);
            $userGroup->addMember($this);
        }

        return $this;
    }

    public function removeUserGroup(UserGroup $userGroup): static
    {
        if ($this->userGroups->removeElement($userGroup)) {
            $userGroup->removeMember($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getResolvedTickets(): Collection
    {
        return $this->resolvedTickets;
    }

    public function addResolvedTicket(Ticket $resolvedTicket): static
    {
        if (!$this->resolvedTickets->contains($resolvedTicket)) {
            $this->resolvedTickets->add($resolvedTicket);
            $resolvedTicket->setResolvedBy($this);
        }

        return $this;
    }

    public function removeResolvedTicket(Ticket $resolvedTicket): static
    {
        if ($this->resolvedTickets->removeElement($resolvedTicket)) {
            // set the owning side to null (unless already changed)
            if ($resolvedTicket->getResolvedBy() === $this) {
                $resolvedTicket->setResolvedBy(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getClosedTickets(): Collection
    {
        return $this->closedTickets;
    }

    public function addClosedTicket(Ticket $closedTicket): static
    {
        if (!$this->closedTickets->contains($closedTicket)) {
            $this->closedTickets->add($closedTicket);
            $closedTicket->setClosedBy($this);
        }

        return $this;
    }

    public function removeClosedTicket(Ticket $closedTicket): static
    {
        if ($this->closedTickets->removeElement($closedTicket)) {
            // set the owning side to null (unless already changed)
            if ($closedTicket->getClosedBy() === $this) {
                $closedTicket->setClosedBy(null);
            }
        }

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): static
    {
        $this->lastName = $lastName;

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

    public function isCanLogIn(): ?bool
    {
        return $this->canLogIn;
    }

    public function setCanLogIn(bool $canLogIn): static
    {
        $this->canLogIn = $canLogIn;

        return $this;
    }

    public function getDefaultStartingPage(): ?string
    {
        return $this->defaultStartingPage;
    }

    public function setDefaultStartingPage(?string $defaultStartingPage): static
    {
        $this->defaultStartingPage = $defaultStartingPage;

        return $this;
    }

    public function getLastLogonDate(): ?\DateTimeImmutable
    {
        return $this->lastLogonDate;
    }

    public function setLastLogonDate(?\DateTimeImmutable $lastLogonDate): static
    {
        $this->lastLogonDate = $lastLogonDate;

        return $this;
    }

    public function getLsatLogonIp(): ?string
    {
        return $this->lsatLogonIp;
    }

    public function setLsatLogonIp(?string $lsatLogonIp): static
    {
        $this->lsatLogonIp = $lsatLogonIp;

        return $this;
    }

    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->roles, true);
    }

    public function getTicketDateFormat(): ?string
    {
        return $this->ticketDateFormat;
    }

    public function setTicketDateFormat(?string $ticketDateFormat): static
    {
        $this->ticketDateFormat = $ticketDateFormat;

        return $this;
    }

    /**
     * @return Collection<int, Device>
     */
    public function getAssignedDevices(): Collection
    {
        return $this->assignedDevices;
    }

    public function addAssignedDevice(Device $assignedDevice): static
    {
        if (!$this->assignedDevices->contains($assignedDevice)) {
            $this->assignedDevices->add($assignedDevice);
            $assignedDevice->setAssignedTo($this);
        }

        return $this;
    }

    public function removeAssignedDevice(Device $assignedDevice): static
    {
        if ($this->assignedDevices->removeElement($assignedDevice)) {
            // set the owning side to null (unless already changed)
            if ($assignedDevice->getAssignedTo() === $this) {
                $assignedDevice->setAssignedTo(null);
            }
        }

        return $this;
    }
}
