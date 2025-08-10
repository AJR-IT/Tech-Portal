<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Entity\UserGroup;
use App\Service\TicketService;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/ticket', name: 'app_ticket_')]
final class TicketController extends AbstractController
{
    public function __construct(TicketService $ticketService)
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
     * @param User $currentUser
     *
     * @return void
     */
    #[Route('/new', name: 'new')]
    public function createTicket(array $data, #[CurrentUser] User $currentUser): void
    {
        $validKeys = [ // This will be used to validate necessary keys exist
            'assigned_user',
            'assigned_group',
            'closed_by',
            'date_due',
            'original_message',
            'requesting_user',
            'resolved_user',
            'subject'
        ];
    }

    /**
     * Updates the provided ticket
     *
     * @param Ticket $ticket
     * @param User $modifiedBy
     *
     * @return void
     */
    #[Route('/update', name: 'update')]
    public function updateTicket(Ticket $ticket, #[CurrentUser] User $modifiedBy): void
    {

    }
}
