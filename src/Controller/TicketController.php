<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Form\NewTicketType;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/new', name: 'new')]
    public function createTicket(Request $request, TicketService $ticketService, #[CurrentUser] User $currentUser): Response
    {
        $form = $this->createForm(NewTicketType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            dd($data);

            $ticket = $ticketService->createTicket([
                'subject' => $data['subject'],
                'requesting_user' => $currentUser,
                'original_message' => $data['originalMessage'],
                'date_due' => $data['dateDue'],
                'tags' => $data['tags'],
            ]);
        }

        return $this->render('ticket/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Updates the provided ticket.
     */
    #[Route('/update', name: 'update')]
    public function updateTicket(Ticket $ticket, #[CurrentUser] User $modifiedBy): void
    {
    }
}
