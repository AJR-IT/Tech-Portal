<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Form\NewTicketType;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/ticket', name: 'app_ticket_')]
final class TicketController extends AbstractController
{
    public function __construct(private readonly TicketService $ticketService)
    {
    }

    #[Route('/', name: 'index')]
    public function index(): Response
    {
        $tickets = $this->ticketService->getTickets();

        return $this->render('ticket/index.html.twig', [
            'tickets' => $tickets,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function createTicket(Request $request, #[CurrentUser] User $currentUser): Response
    {
        $form = $this->createForm(NewTicketType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $ticket = $this->ticketService->createTicket([
                'subject' => $data['subject'],
                'requested_by' => $currentUser,
                'original_message' => $data['originalMessage'],
                'date_due' => $data['dateDue'],
                'tags' => $data['tags'],
            ]);
        }

        return $this->render('ticket/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Update a ticket.
     */
    #[Route('/update', name: 'update')]
    public function updateTicket(Ticket $ticket, #[CurrentUser] User $updatedBy): RedirectResponse
    {
        $ticket = $this->ticketService->updateTicket($ticket, $updatedBy);

        return $this->redirectToRoute('app_ticket_show', ['id' => $ticket]);
    }

    /**
     * Show a ticket.
     */
    #[Route('/{id}', name: 'show')]
    public function showTicket(int $id): Response
    {
        $ticket = $this->ticketService->getTicket($id);

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }
}
