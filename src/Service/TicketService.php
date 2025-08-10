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
        $validKeys = [
            'assigned_user',
            'assigned_group',
            'closed_by',
            'date_due',
            'original_message',
            'requesting_user',
            'resolved_user',
            'subject'
        ];

        // TODO validate input, does it have to be here?

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

            $ticketHistory = new TicketHistory();

            $ticketHistory
                ->setTicket($ticket)
                ->setDateCreated(new DateTimeImmutable('now'))
                ->setRelatedUser($ticket->getRequestingUser())
                ->setMessage(TicketAction::CREATED)
            ;

            $this->entityManager->persist($ticketHistory);

            $ticket->addTicketHistory($ticketHistory);

            $this->entityManager->persist($ticket);
            $this->entityManager->flush();
        } catch (Exception) {
            return null;
        }

        return $ticket;
    }

    public function updateTicket(Ticket $ticket, User $currentUser): Ticket
    {
        $ticket->setDateModified(new DateTime('now'));
        // TODO add property to entity
        // $ticket->setModifiedBy($currentUser);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return $ticket;
    }

    public function deleteTicket(Ticket $ticket): void {}

    public function closeTicket(Ticket $ticket): void {}

    public function archiveTicket(Ticket $ticket): void {}
}
