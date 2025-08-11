<?php

namespace App\Service;

use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\TicketAction;
use App\Entity\TicketHistory;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Repository\StatusRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;

final readonly class TicketService
{
    public function __construct(private EntityManagerInterface $entityManager, private StatusRepository $statusRepository)
    {
    }

    /**
     * Create a new ticket
     *
     * @param array{assigned_user: User, assigned_group: UserGroup, closed_by: User, date_due: DateTimeImmutable, original_message: string, requesting_user: User, resolved_user: User, subject: string} $data
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
                ->setDateCreated(new DateTimeImmutable('now'))
                ->setAssignedGroup($data['assigned_group'] ?? null)
                ->setAssignedUser($data['assigned_user'] ?? null)
                ->setClosedBy($data['closed_by'] ?? null)
                ->setClosedDate((!is_null($data['closed_by'])) ? new DateTimeImmutable('now') : null)
                ->setDateDue($data['date_due'] ?? null)
                ->setDateModified(new DateTimeImmutable('now'))
                ->setOriginalMessage($data['original_message'] ?? null)
                ->setRequestingUser($data['requesting_user'] ?? null)
                ->setResolvedBy($data['resolved_user'] ?? null)
                ->setResolvedDate((!is_null($data['resolved_user'])) ? new DateTimeImmutable('now') : null)
                ->setSubject($data['subject'] ?? null)
                ->setStatus($status)
            ;

            $this->entityManager->persist($ticket);

            $ticket->addTicketHistory(
                $this->addTicketHistory($ticket, $ticket->getREquestingUser(), TicketAction::CREATED)
            );

            $this->entityManager->flush();
        } catch (Exception) {
            return null;
        }

        return $ticket;
    }

    /**
     * Update a ticket
     *
     * @param Ticket $ticket The Ticket::class entity to be updated
     * @param User $updatedBy The User::class that called the update
     *
     * @return Ticket|null The Ticket::class entity that was updated or null on failure
     */
    public function updateTicket(Ticket $ticket, User $updatedBy): ?Ticket
    {
        $previousTicket = $this->entityManager->getRepository(Ticket::class)->find($ticket->getId());

        try {
            $ticket->setDateModified(new DateTimeImmutable('now'));

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
        } catch (Exception) {
            return null;
        }

        return $ticket;
    }

    /**
     * Delete a ticket
     *
     * @param Ticket $ticket The Ticket::class entity to be removed
     *
     * @return void
     */
    public function deleteTicket(Ticket $ticket): void
    {
        $this->entityManager->remove($ticket);

        $this->entityManager->flush();
    }

    /**
     * Change status of ticket
     *
     * @param Ticket $ticket The Ticket::class entity to be updated
     * @param Status $status The Status::class entity to set
     * @param User $updatedBy The User::class entity that called the update
     *
     * @return Ticket|null The Ticket::class entity or null failure
     */
    public function changeStatus(Ticket $ticket, Status $status, User $updatedBy): ?Ticket
    {
        try {
            $ticket->setDateModified(new DateTimeImmutable('now'));

            $ticket->setStatus($status);

            $ticket->setModifiedBy($updatedBy);

            $this->entityManager->persist($ticket);

            $ticket->addTicketHistory(
                $this->addTicketHistory($ticket, $updatedBy, TicketAction::UPDATED, 'Changed status')
            );
        } catch (Exception) {
            return null;
        }

        return $ticket;
    }

    /**
     * Close a ticket
     *
     * @param Ticket $ticket The Ticket::class entity to close
     * @param User $closedBy The User::class entity that called the close
     *
     * @return Ticket The Ticket::class that was closed
     */
    public function closeTicket(Ticket $ticket, User $closedBy): Ticket
    {
        $ticket->setClosedBy($closedBy);

        $ticket->setClosedDate(new DateTimeImmutable('now'));

        $this->entityManager->persist($ticket);

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $closedBy, TicketAction::CLOSED)
        );

        $this->changeStatus($ticket, $this->statusRepository->findOneBy(['fullName' => 'Closed']), $closedBy);

        return $ticket;
    }

    /**
     * Close multiple tickets
     *
     * @param (Ticket|int)[] $tickets An array Ticket::class or int
     * @param User $closedBy The User::class that called the close
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
     * Resolve a ticket
     *
     * @param Ticket $ticket The Ticket:class entity to be resolved
     * @param User $resolvedBy The User::class that called the resolved
     *
     * @return Ticket The Ticket::entity that was resolved
     */
    public function resolveTicket(Ticket $ticket, User $resolvedBy): Ticket
    {
        $ticket->setResolvedBy($resolvedBy);

        $ticket->setResolvedDate(new DateTimeImmutable());

        $this->entityManager->persist($ticket);

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $resolvedBy, TicketAction::RESOLVED)
        );

        $this->entityManager->flush();

        $this->changeStatus($ticket, $this->statusRepository->findOneBy(['fullName' => 'Resolved']), $resolvedBy);

        return $ticket;
    }

    /**
     * Resolve multiple tickets
     *
     * @param (Ticket|int)[] $tickets An array of Ticket::class or int to be resolved
     * @param User $resolvedBy The User::class that called the resolve
     *
     * @return (Ticket|int)[] An array of either Ticket::class or ticket ids, whichever was provided to the method
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
     * Cancel a ticket
     *
     * @param Ticket $ticket The Ticket:class entity to be resolved
     * @param User $cancelledBy The User::class that called the resolved
     *
     * @return Ticket The Ticket::entity that was resolved
     */
    public function cancelTicket(Ticket $ticket, User $cancelledBy): Ticket
    {
        $ticket->setCancelledBy($cancelledBy);

        $ticket->setCancelledDate(new DateTimeImmutable());

        $this->entityManager->persist($ticket);

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $cancelledBy, TicketAction::CANCELLED)
        );

        $this->entityManager->flush();

        $this->changeStatus($ticket, $this->statusRepository->findOneBy(['fullName' => 'Cancelled']), $cancelledBy);

        return $ticket;
    }

    /**
     * Cancel multiple tickets
     *
     * @param (Ticket|int)[] $tickets An array of Ticket::class or int to be cancelled
     * @param User $cancelledBy The User::class that called the cancellation
     *
     * @return (Ticket|int)[] An array of either Ticket::class or ticket ids, whichever was provided to the method
     */
    public function cancelTickets(array $tickets, User $cancelledBy): array
    {
        foreach ($tickets as $ticket) {
            if ($ticket instanceof Ticket) {
                $this->cancelTicket($ticket, $cancelledBy);
            } elseif ($this->ensurePositiveInteger($ticket)) {
                $this->cancelTicket(
                    $this->entityManager->getRepository(Ticket::class)->find($ticket),
                    $cancelledBy
                );
            }
        }

        return $tickets;
    }

    /**
     * Get a ticket
     *
     * @param int|Ticket|null $ticketId The Ticket::class entity or an id of an entity. Provide null to use a custom filter
     * @param array $filter Specify a filter array, default is an empty array ([])
     *
     * @throws InvalidArgumentException If you do not provide a $ticketId, you must provide a \$filter[]
     *
     * @return Ticket|null The Ticket::class entity if found or null if not found
     */
    public function getTicket(int|Ticket|null $ticketId = null, array $filter = []): ?Ticket
    {
        if (is_null($ticketId) && !$filter) {
            throw new InvalidArgumentException('If you do not provide a $ticketId, you must provide a $filter[].');
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
     * Get multiple tickets
     *
     * @param array $filter Provide an array of filters
     *
     * @return Ticket[]|array[] Returns either an array of Ticket::class or empty if none found
     */
    public function getTickets(array $filter = []): array
    {
        if ((count($filter) > 0) && $filter[0] instanceof Ticket) {
            return $this->entityManager->getRepository(Ticket::class)->findByEntityArray($filter);
        }

        if ((count($filter) > 0) && filter_var($filter[0], FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]])) {
            return $this->entityManager->getRepository(Ticket::class)->findBy(['id' => $filter]);
        }

        return $this->entityManager->getRepository(Ticket::class)->findBy($filter);
    }

    /**
     *
     * @param Ticket $ticket
     * @param User $relatedUser
     * @param string $subject
     * @param string|null $message
     * @return TicketHistory
     */
    protected function addTicketHistory(Ticket $ticket, User $relatedUser, string $subject, ?string $message = null): TicketHistory
    {
        $ticketHistory = new TicketHistory();

        $ticketHistory
            ->setTicket($ticket)
            ->setDateCreated(new DateTimeImmutable('now'))
            ->setRelatedUser($relatedUser)
            ->setSubject($subject)
            ->setMessage($message);
        ;

        $this->entityManager->persist($ticketHistory);

        $this->entityManager->flush();

        return $ticketHistory;
    }

    /**
     * @param mixed $int
     * @return bool
     */
    protected function ensurePositiveInteger(mixed $int): bool
    {
        return filter_var($int, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
    }
}
