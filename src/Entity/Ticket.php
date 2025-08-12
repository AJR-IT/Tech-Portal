<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dateCreated = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateModified = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $dateDue = null;

    #[ORM\Column(length: 255)]
    private ?string $subject = null;

    #[ORM\Column(length: 255)]
    private ?string $originalMessage = null;

    #[ORM\ManyToOne(inversedBy: 'ticketsSubmitted')]
    private ?User $requestingUser = null;

    #[ORM\ManyToOne(inversedBy: 'assignedTickets')]
    private ?User $assignedUser = null;

    /**
     * @var Collection<int, Comment>
     */
    #[ORM\OneToMany(targetEntity: Comment::class, mappedBy: 'ticket')]
    private Collection $comments;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    private ?UserGroup $assignedGroup = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $resolvedDate = null;

    #[ORM\ManyToOne(inversedBy: 'resolvedTickets')]
    private ?User $resolvedBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $closedDate = null;

    #[ORM\ManyToOne(inversedBy: 'closedTickets')]
    private ?User $closedBy = null;

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'tickets')]
    private Collection $tags;

    /**
     * @var Collection<int, TicketHistory>
     */
    #[ORM\OneToMany(targetEntity: TicketHistory::class, mappedBy: 'ticket', orphanRemoval: true)]
    private Collection $ticketHistory;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status;

    #[ORM\ManyToOne]
    private ?User $modifiedBy = null;

    #[ORM\ManyToOne]
    private ?User $canceledBy = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $canceledDate = null;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->ticketHistory = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDateModified(): ?\DateTimeImmutable
    {
        return $this->dateModified;
    }

    public function setDateModified(?\DateTimeImmutable $dateModified): static
    {
        $this->dateModified = $dateModified;

        return $this;
    }

    public function getDateDue(): ?\DateTimeImmutable
    {
        return $this->dateDue;
    }

    public function setDateDue(?\DateTimeImmutable $dateDue): static
    {
        $this->dateDue = $dateDue;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): static
    {
        $this->subject = $subject;

        return $this;
    }

    public function getOriginalMessage(): ?string
    {
        return $this->originalMessage;
    }

    public function setOriginalMessage(string $originalMessage): static
    {
        $this->originalMessage = $originalMessage;

        return $this;
    }

    public function getRequestingUser(): ?User
    {
        return $this->requestingUser;
    }

    public function setRequestingUser(?User $requestingUser): static
    {
        $this->requestingUser = $requestingUser;

        return $this;
    }

    public function getAssignedUser(): ?User
    {
        return $this->assignedUser;
    }

    public function setAssignedUser(?User $assignedUser): static
    {
        $this->assignedUser = $assignedUser;

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
            $comment->setTicket($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): static
    {
        if ($this->comments->removeElement($comment)) {
            // set the owning side to null (unless already changed)
            if ($comment->getTicket() === $this) {
                $comment->setTicket(null);
            }
        }

        return $this;
    }

    public function getAssignedGroup(): ?UserGroup
    {
        return $this->assignedGroup;
    }

    public function setAssignedGroup(?UserGroup $assignedGroup): static
    {
        $this->assignedGroup = $assignedGroup;

        return $this;
    }

    public function getResolvedDate(): ?\DateTimeImmutable
    {
        return $this->resolvedDate;
    }

    public function setResolvedDate(?\DateTimeImmutable $resolvedDate): static
    {
        $this->resolvedDate = $resolvedDate;

        return $this;
    }

    public function getResolvedBy(): ?User
    {
        return $this->resolvedBy;
    }

    public function setResolvedBy(?User $resolvedBy): static
    {
        $this->resolvedBy = $resolvedBy;

        return $this;
    }

    public function getClosedDate(): ?\DateTimeImmutable
    {
        return $this->closedDate;
    }

    public function setClosedDate(?\DateTimeImmutable $closedDate): static
    {
        $this->closedDate = $closedDate;

        return $this;
    }

    public function getClosedBy(): ?User
    {
        return $this->closedBy;
    }

    public function setClosedBy(?User $closedBy): static
    {
        $this->closedBy = $closedBy;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, TicketHistory>
     */
    public function getTicketHistory(): Collection
    {
        return $this->ticketHistory;
    }

    public function addTicketHistory(TicketHistory $ticketHistory): static
    {
        if (!$this->ticketHistory->contains($ticketHistory)) {
            $this->ticketHistory->add($ticketHistory);
            $ticketHistory->setTicket($this);
        }

        return $this;
    }

    public function removeTicketHistory(TicketHistory $ticketHistory): static
    {
        if ($this->ticketHistory->removeElement($ticketHistory)) {
            // set the owning side to null (unless already changed)
            if ($ticketHistory->getTicket() === $this) {
                $ticketHistory->setTicket(null);
            }
        }

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getModifiedBy(): ?User
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(?User $modifiedBy): static
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    public function getCanceledBy(): ?User
    {
        return $this->canceledBy;
    }

    public function setCanceledBy(?User $canceledBy): static
    {
        $this->canceledBy = $canceledBy;

        return $this;
    }

    public function getCanceledDate(): ?\DateTimeImmutable
    {
        return $this->canceledDate;
    }

    public function setCanceledDate(?\DateTimeImmutable $cancelledDate): static
    {
        $this->canceledDate = $cancelledDate;

        return $this;
    }
}
