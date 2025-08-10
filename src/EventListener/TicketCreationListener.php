<?php

namespace App\EventListener;

use App\Entity\Ticket;
use App\Entity\TicketAction;
use App\Entity\TicketHistory;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Ticket::class)]
final class TicketCreationListener
{
    public function postPersist(Ticket $ticket, PostUpdateEventArgs $eventArgs): void
    {
        $ticketHistory = new TicketHistory();

        $ticketHistory
            ->setTicket($ticket)
            ->setDateCreated(new DateTimeImmutable('now'))
            ->setRelatedUser($ticket->getRequestingUser())
            ->setMessage(TicketAction::CREATED)
        ;

        $eventArgs->getObjectManager()->persist($ticketHistory);
        $eventArgs->getObjectManager()->flush();
    }
}
