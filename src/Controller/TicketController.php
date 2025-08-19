<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\User;
use App\Form\NewTicketType;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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

            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket?->getId()]);
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
    public function showTicket(StatusRepository $statusRepository, UserRepository $userRepository, int $id): Response
    {
        //        $assignableUsers = $userRepository->getAssignableUsers('ticket');
        $assignableUsers = $userRepository->findAll();
        $statuses = $statusRepository->findAll();

        $ticket = $this->ticketService->getTicket($id);

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
            'assignableUsers' => $assignableUsers,
            'statuses' => $statuses,
        ]);
    }

    #[Route('/update/assign', name: 'update_assign', methods: ['POST'])]
    public function ajaxAssignUser(Request $request, TicketService $ticketService, UserRepository $userRepository, #[CurrentUser] User $currentUser): JsonResponse
    {
        $userId = (int) $request->request->get('userId');

        $ticketId = (int) $request->request->get('ticketId');

        if (!$userId || !$ticketId) {
            return new JsonResponse([
                'success' => false,
                'payload' => [
                    'message' => 'Invalid user id or ticket id',
                    'data' => [
                        'ticketId' => $ticketId,
                        'userId' => $userId,
                    ],
                ],
            ]);
        }

        $user = $userRepository->find($userId);

        if (!$user) {
            return new JsonResponse([
                'success' => false,
                'payload' => [
                    'message' => 'User not found',
                    'data' => [
                        'ticketId' => $ticketId,
                        'userId' => $userId,
                    ],
                ],
            ]);
        }

        $ticket = $ticketService->getTicket($ticketId);

        if (!$ticket) {
            return new JsonResponse([
                'success' => false,
                'payload' => [
                    'message' => 'Ticket not found',
                    'data' => [
                        'ticketId' => $ticketId,
                        'userId' => $userId,
                    ],
                ],
            ]);
        }

        $ticket->setAssignedUser($user);

        $ticket = $ticketService->updateTicket($ticket, $currentUser);

        if ($ticket?->getAssignedUser()?->getId() !== $userId) {
            return new JsonResponse([
                'success' => false,
                'payload' => [
                    'message' => 'User not assigned successfully',
                    'data' => [
                        'ticketId' => $ticketId,
                        'userId' => $userId,
                    ],
                ],
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'payload' => [
                'message' => 'User assigned successfully',
                'data' => [
                    'ticketId' => $ticketId,
                    'userId' => $userId,
                    'user' => [
                        'firstName' => $user->getFirstName(),
                        'lastName' => $user->getLastName(),
                    ],
                ],
            ],
        ]);
    }
}
