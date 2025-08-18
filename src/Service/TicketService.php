<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\TicketAction;
use App\Entity\TicketHistory;
use App\Entity\User;
use App\Repository\StatusRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TicketService
{
    public function __construct(private EntityManagerInterface $entityManager, private StatusRepository $statusRepository)
    {
    }

    /**
     * Create a new ticket.
     *
     * @return Ticket|null Will return the Ticket::class entity or null on failure
     */
    public function createTicket(array $data): ?Ticket
    {
        $ticket = new Ticket();

        /** @var Status $status */
        $status = $this->statusRepository->findOneBy(['fullName' => 'Open']);

        try {
            $ticket
                ->setAssignedGroup($data['assigned_group'] ?? null)
                ->setAssignedUser($data['assigned_to'] ?? null)
                ->setDateDue($data['date_due'] ?? null)
                ->setOriginalMessage($data['original_message'] ?? null)
                ->setRequestingUser($data['requested_by'] ?? null)
                ->setModifiedBy($data['requested_by'] ?? null)
                ->setResolvedBy($data['resolved_by'] ?? null)
                ->setSubject($data['subject'] ?? null)
                ->setStatus($status)
                ->setDateModified(new \DateTimeImmutable())
                ->setDateCreated(new \DateTimeImmutable())
            ;

            if (array_key_exists('closed_by', $data)) {
                $ticket->setClosedBy($data['closed_by'])
                    ->setClosedDate(new \DateTimeImmutable());
            }

            if (array_key_exists('resolved_by', $data)) {
                $ticket->setResolvedBy($data['resolved_by'])
                    ->setResolvedDate(new \DateTimeImmutable());
            }

            if (array_key_exists('tags', $data)) {
                foreach ($data['tags'] as $tag) {
                    $ticket->addTag($tag);
                }
            }

            $this->entityManager->persist($ticket);

            $ticket->addTicketHistory(
                $this->addTicketHistory($ticket, $ticket->getREquestingUser(), TicketAction::CREATED)
            );

            $this->entityManager->flush();
        } catch (\Exception) {
            return null;
        }

        return $ticket;
    }

    /**
     * Update a ticket.
     *
     * @param Ticket $ticket    The Ticket::class entity to be updated
     * @param User   $updatedBy The User::class that called the update
     *
     * @return Ticket|null The Ticket::class entity that was updated or null on failure
     */
    public function updateTicket(Ticket $ticket, User $updatedBy): ?Ticket
    {
        $previousTicket = $this->entityManager->getRepository(Ticket::class)->find($ticket->getId());

        try {
            $ticket->setDateModified(new \DateTimeImmutable('now'));

            $ticket->setModifiedBy($updatedBy);

            $this->entityManager->persist($ticket);

            $ticket->addTicketHistory(
                $this->addTicketHistory($ticket, $updatedBy, TicketAction::UPDATED)
            );

            if ($previousTicket->getStatus() !== $ticket->getStatus()) {
                $ticket->addTicketHistory(
                    $this->addTicketHistory(
                        $ticket,
                        $updatedBy,
                        TicketAction::UPDATED,
                        sprintf('Status changed to %s', $ticket->getStatus()?->getFriendlyname()))
                );
            }

            $this->entityManager->flush();
        } catch (\Exception) {
            return null;
        }

        return $ticket;
    }

    /**
     * Delete a ticket.
     *
     * @param Ticket $ticket The Ticket::class entity to be removed
     */
    public function deleteTicket(Ticket $ticket): void
    {
        $this->entityManager->remove($ticket);

        $this->entityManager->flush();
    }

    /**
     * Change status of a ticket.
     *
     * @param Ticket $ticket    The Ticket::class entity to be updated
     * @param Status $status    The Status::class entity to set
     * @param User   $updatedBy The User::class entity that called the update
     *
     * @return Ticket|null The Ticket::class entity or null failure
     */
    public function changeStatus(Ticket $ticket, Status $status, User $updatedBy): ?Ticket
    {
        try {
            $ticket->setDateModified(new \DateTimeImmutable('now'));

            $ticket->setStatus($status);

            $ticket->setModifiedBy($updatedBy);

            $this->entityManager->persist($ticket);

            $ticket->addTicketHistory(
                $this->addTicketHistory($ticket, $updatedBy, TicketAction::UPDATED, 'Changed status')
            );
        } catch (\Exception) {
            return null;
        }

        return $ticket;
    }

    /**
     * Close a ticket.
     *
     * @param Ticket $ticket   The Ticket::class entity to close
     * @param User   $closedBy The User::class entity that called the close
     *
     * @return Ticket The Ticket::class that was closed
     */
    public function closeTicket(Ticket $ticket, User $closedBy): Ticket
    {
        $ticket->setClosedBy($closedBy);

        $ticket->setClosedDate(new \DateTimeImmutable('now'));

        $this->entityManager->persist($ticket);

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $closedBy, TicketAction::CLOSED)
        );

        $this->changeStatus($ticket, $this->statusRepository->findOneBy(['fullName' => 'Closed']), $closedBy);

        return $ticket;
    }

    /**
     * Close multiple tickets.
     *
     * @param (Ticket|int)[] $tickets  An array Ticket::class or int
     * @param User           $closedBy The User::class that called the close
     *
     * @return Ticket[] An array of Ticket::class entities
     */
    public function closeTickets(array $tickets, User $closedBy): array
    {
        foreach ($tickets as $ticket) {
            if ($ticket instanceof Ticket) {
                $this->closeTicket($ticket, $closedBy);
            } elseif ($this->ensurePositiveInteger($ticket)) {
                $this->closeTicket(
                    $this->entityManager->getRepository(Ticket::class)->find($ticket),
                    $closedBy
                );
            }
        }

        return $this->getTickets($tickets);
    }

    /**
     * Resolve a ticket.
     *
     * @param Ticket $ticket     The Ticket:class entity to be resolved
     * @param User   $resolvedBy The User::class that called the resolved
     *
     * @return Ticket The Ticket::entity that was resolved
     */
    public function resolveTicket(Ticket $ticket, User $resolvedBy): Ticket
    {
        $ticket->setResolvedBy($resolvedBy);

        $ticket->setResolvedDate(new \DateTimeImmutable());

        $this->entityManager->persist($ticket);

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $resolvedBy, TicketAction::RESOLVED)
        );

        $this->entityManager->flush();

        $this->changeStatus($ticket, $this->statusRepository->findOneBy(['fullName' => 'Resolved']), $resolvedBy);

        return $ticket;
    }

    /**
     * Resolve multiple tickets.
     *
     * @param (Ticket|int)[] $tickets    An array of Ticket::class or int to be resolved
     * @param User           $resolvedBy The User::class that called the resolve
     *
     * @return Ticket[] An array of Ticket::class entities
     */
    public function resolveTickets(array $tickets, User $resolvedBy): array
    {
        foreach ($tickets as $ticket) {
            if ($ticket instanceof Ticket) {
                $this->resolveTicket($ticket, $resolvedBy);
            } elseif ($this->ensurePositiveInteger($ticket)) {
                $this->resolveTicket(
                    $this->entityManager->getRepository(Ticket::class)->find($ticket),
                    $resolvedBy
                );
            }
        }

        return $this->getTickets($tickets);
    }

    /**
     * Cancel a ticket.
     *
     * @param Ticket $ticket     The Ticket:class entity to be resolved
     * @param User   $canceledBy The User::class that called the resolved
     *
     * @return Ticket The Ticket::entity that was resolved
     */
    public function cancelTicket(Ticket $ticket, User $canceledBy): Ticket
    {
        $ticket->setcanceledBy($canceledBy);

        $ticket->setCanceledDate(new \DateTimeImmutable());

        $this->entityManager->persist($ticket);

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $canceledBy, TicketAction::CANCELED)
        );

        $this->entityManager->flush();

        $this->changeStatus($ticket, $this->statusRepository->findOneBy(['fullName' => 'Canceled']), $canceledBy);

        return $ticket;
    }

    /**
     * Cancel multiple tickets.
     *
     * @param (Ticket|int)[] $tickets    An array of Ticket::class or int to be canceled
     * @param User           $canceledBy The User::class that called the cancellation
     *
     * @return Ticket[] An array of Ticket::class entities
     */
    public function cancelTickets(array $tickets, User $canceledBy): array
    {
        foreach ($tickets as $ticket) {
            if ($ticket instanceof Ticket) {
                $this->cancelTicket($ticket, $canceledBy);
            } elseif ($this->ensurePositiveInteger($ticket)) {
                $this->cancelTicket(
                    $this->entityManager->getRepository(Ticket::class)->find($ticket),
                    $canceledBy
                );
            }
        }

        return $this->getTickets($tickets);
    }

    /**
     * Get a ticket.
     *
     * @param int|Ticket|null $ticketId The Ticket::class entity or an id of an entity. Provide null to use a custom filter
     * @param array           $filter   Specify a filter array, default is an empty array ([])
     *
     * @return Ticket|null The Ticket::class entity if found or null if not found
     *
     * @throws \InvalidArgumentException If you do not provide a $ticketId, you must provide a \$filter[]
     */
    public function getTicket(int|Ticket|null $ticketId = null, array $filter = []): ?Ticket
    {
        if (is_null($ticketId) && !$filter) {
            throw new \InvalidArgumentException('If you do not provide a $ticketId, you must provide a $filter[].');
        }

        if ($ticketId instanceof Ticket) {
            return $ticketId;
        }

        if ($this->ensurePositiveInteger($ticketId)) {
            $filter = ['id' => $ticketId];
        }

        return $this->entityManager->getRepository(Ticket::class)->findOneBy($filter);
    }

    /**
     * Get multiple tickets.
     *
     * @param array $filter Provide an array of filters
     *
     * @return Ticket[]|array[] Returns either an array of Ticket::class or empty if none found
     */
    public function getTickets(array $filter = []): array
    {
        if ((count($filter) > 0) && $filter[array_rand($filter)] instanceof Ticket) {
            return $this->entityManager->getRepository(Ticket::class)->findByEntityArray($filter);
        }

        if ((count($filter) > 0) && $this->ensurePositiveInteger($filter[array_rand($filter)])) {
            return $this->entityManager->getRepository(Ticket::class)->findBy(['id' => $filter]);
        }

        return $this->entityManager->getRepository(Ticket::class)->findBy($filter);
    }

    /**
     * Add a comment to a ticket.
     */
    public function addComment(Ticket $ticket, Comment $comment): Ticket
    {
        $ticket->addComment($comment);

        return $this->updateTicket($ticket, $comment->getCreatedByUser());
    }

    /**
     * Retrieve comments for a ticket.
     *
     * @param Ticket $ticket      The Ticket::class entity to retrieve comments for
     * @param User   $currentUser The User::class entity that is currently logged in
     *
     * @return Collection<int, Comment>|array<int, Comment> Returns either a Collection of Comment::class or an array of Comment::class
     */
    public function getComments(Ticket $ticket, User $currentUser): Collection|array
    {
        $ticketComments = $ticket->getComments();

        if ($currentUser->isAdmin() || ($ticket->getAssignedUser() === $currentUser)) {
            return $ticketComments;
        }

        $comments = [];

        foreach ($ticketComments as $comment) {
            if ($comment->isPublished()) {
                $comments[] = $comment;
            }
        }

        return $comments;
    }

    protected function addTicketHistory(Ticket $ticket, User $relatedUser, string $subject, ?string $message = null): TicketHistory
    {
        $ticketHistory = new TicketHistory();

        $ticketHistory
            ->setTicket($ticket)
            ->setDateCreated(new \DateTimeImmutable('now'))
            ->setRelatedUser($relatedUser)
            ->setSubject($subject)
            ->setMessage($message);

        $this->entityManager->persist($ticketHistory);

        $this->entityManager->flush();

        return $ticketHistory;
    }

    protected function ensurePositiveInteger(mixed $int): bool
    {
        return filter_var($int, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    }
}
