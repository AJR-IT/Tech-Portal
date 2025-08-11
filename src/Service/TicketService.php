<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\TicketAction;
use App\Entity\TicketHistory;
use App\Entity\User;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class TicketService
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function createTicket(array $data): ?Ticket
    {
        try {
            $ticket = new Ticket();
            $ticket->setDateCreated(new DateTimeImmutable('now'))
                ->setAssignedGroup($data['assigned_group'] ?? null)
                ->setAssignedUser($data['assigned_user'] ?? null)
                ->setClosedBy($data['closed_by'] ?? null)
                ->setClosedDate((!is_null($data['closed_by'])) ? new DateTimeImmutable('now') : null)
                ->setDateDue($data['date_due'] ?? null)
                ->setDateModified(new DateTime('now'))
                ->setOriginalMessage($data['original_message'] ?? null)
                ->setRequestingUser($data['requesting_user'] ?? null)
                ->setResolvedBy($data['resolved_user'] ?? null)
                ->setResolvedDate((!is_null($data['resolved_user'])) ? new DateTimeImmutable('now') : null)
                ->setSubject($data['subject'] ?? null)
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

    public function updateTicket(Ticket $ticket, User $updatedBy): Ticket
    {
        $ticket->setDateModified(new DateTime('now'));
        // TODO add property to entity
        // $ticket->setModifiedBy($updatedBy);

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $updatedBy, TicketAction::UPDATED)
        );

        $this->entityManager->persist($ticket);

        $this->entityManager->flush();

        return $ticket;
    }

    public function deleteTicket(Ticket $ticket): void
    {
        $this->entityManager->remove($ticket);
        $this->entityManager->flush();
    }

    public function closeTicket(Ticket $ticket, User $closedBy): Ticket
    {
        $ticket->setClosedBy($closedBy);

        $ticket->setClosedDate(new DateTimeImmutable('now'));

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $closedBy, TicketAction::CLOSED)
        );

        $this->entityManager->persist($ticket);

        $this->entityManager->flush();

        return $ticket;
    }

    public function resolveTicket(Ticket $ticket, User $resolvedBy): Ticket
    {
        $ticket->setResolvedBy($resolvedBy);

        $ticket->setResolvedDate(new DateTimeImmutable());

        $ticket->addTicketHistory(
            $this->addTicketHistory($ticket, $resolvedBy, TicketAction::RESOLVED)
        );

        $this->entityManager->persist($ticket);

        $this->entityManager->flush();

        return $ticket;
    }

    public function getTicket(int|Ticket $ticketId): ?Ticket
    {
        if ($ticketId instanceof Ticket) {
            return $ticketId;
        }

        return $this->entityManager->getRepository(Ticket::class)->find($ticketId);
    }

    public function getTickets(): array
    {
        return $this->entityManager->getRepository(Ticket::class)->findAll();
    }

    private function addTicketHistory(Ticket $ticket, User $relatedUser, string $message): TicketHistory
    {
        $ticketHistory = new TicketHistory();

        $ticketHistory
            ->setTicket($ticket)
            ->setDateCreated(new DateTimeImmutable('now'))
            ->setRelatedUser($relatedUser)
            ->setMessage($message)
        ;

        $this->entityManager->persist($ticketHistory);

        $this->entityManager->flush();

        return $ticketHistory;
    }
}
