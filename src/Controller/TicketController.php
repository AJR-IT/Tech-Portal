<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\UserGroup;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ticket', name: 'app_ticket_')]
final class TicketController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/index', name: 'index')]
    public function index(): Response
    {
        return $this->render('ticket/index.html.twig', [
            'controller_name' => 'TicketController',
        ]);
    }

    /**
     * Create a new ticket
     * @param array{assigned_user: User, assigned_group: UserGroup, closed_by: User, date_due: DateTimeImmutable, original_message: string, requesting_user: User, resolved_user: User, subject: string} $data
     *
     * @return void
     */
    private function createTicket(array $data): void
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
    }

    /**
     * Updates the provided ticket
     *
     * @param Ticket $ticket
     * @param User $modifiedBy
     *
     * @return void
     */
    private function updateTicket(Ticket $ticket, User $modifiedBy): void
    {
        $ticket->setDateModified(new DateTime('now'));
        // TODO add property to entity
        // $ticket->setModifiedBy($currentUser);
        $this->entityManager->persist($ticket);
        $this->entityManager->flush();
    }
}
