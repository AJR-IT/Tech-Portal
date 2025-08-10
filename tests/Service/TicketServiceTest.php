<?php

namespace App\Tests\Service;

use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TicketService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TicketServiceTest extends KernelTestCase
{
    public function testCreateTicket(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $ticketService = $container->get(TicketService::class);
        $userRepository = $container->get(UserRepository::class);

        $testUser = $userRepository->findOneBy(['username' => 'test']);

        $data = [
            'assigned_user' => null,
            'assigned_group' => null,
            'closed_by' => null,
            'date_due' => null,
            'original_message' => 'This is a test message',
            'requesting_user' => $testUser,
            'resolved_user' => null,
            'subject' => 'This is a test subject'
        ];

        $ticket = $ticketService->createTicket($data);

        $this->assertInstanceOf(Ticket::class, $ticket, '$ticket should be an instance of Ticket');

        $this->assertInstanceOf(User::class, $ticket->getRequestingUser(), 'Requesting user should be assigned.');

        $this->assertInstanceOf(TicketHistory::class, $ticket->getTicketHistory()[0], 'Ticket history should be created on ticket creation.');
    }
}
