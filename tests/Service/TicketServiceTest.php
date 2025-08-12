<?php

namespace App\Tests\Service;

use App\Entity\Status;
use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Entity\User;
use App\Repository\StatusRepository;
use App\Repository\UserRepository;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TicketServiceTest extends KernelTestCase
{
    private TicketService $ticketService;
    private StatusRepository $statusRepository;

    private User $testRequestUser;
    private User $testTechnicianUser;
    private array $testTicketData;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();

        $this->ticketService = $container->get(TicketService::class);

        $this->statusRepository = $container->get(StatusRepository::class);

        $userRepository = $container->get(UserRepository::class);

        $this->testRequestUser = $userRepository->findOneBy(['username' => 'testRequester']);

        $this->testTechnicianUser = $userRepository->findOneBy(['username' => 'testTechnician']);

        $this->testTicketData = [
            'assigned_user' => null,
            'assigned_group' => null,
            'closed_by' => null,
            'date_due' => null,
            'original_message' => 'This is a test message',
            'requesting_user' => $this->testRequestUser,
            'resolved_user' => null,
            'subject' => 'This is a test subject',
        ];
    }

    protected function tearDown(): void
    {
        $this->truncateEntities([
            Ticket::class,
            TicketHistory::class,
        ]);
    }

    protected function truncateEntities(array $entities): void
    {
        foreach ($entities as $entity) {
            $e = $this->entityManager->getRepository($entity);

            foreach ($e->findAll() as $r) {
                $this->entityManager->remove($r);
            }
        }

        $this->entityManager->flush();
    }

    public function testCreateTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->assertInstanceOf(Ticket::class, $ticket, '$ticket should be an instance of Ticket');

        $this->assertInstanceOf(User::class, $ticket->getRequestingUser(), 'Requesting user should be assigned.');

        $this->assertEquals('testRequester', $ticket->getRequestingUser()->getUsername(), 'Requesting user should be \'testRequester\'.');

        $this->assertInstanceOf(TicketHistory::class, $ticket->getTicketHistory()[0], 'Ticket history should be created on ticket creation.');

        $this->assertEquals('Open', $ticket->getStatus()->getFriendlyName(), 'Status should be \'Open\'.');
    }

    public function testUpdateTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $previousDateTime = $ticket->getDateModified();

        $ticket->setAssignedUser($this->testTechnicianUser);

        $this->ticketService->updateTicket($ticket, $this->testTechnicianUser);

        $this->assertEquals('testTechnician', $ticket->getAssignedUser()->getUsername(), 'Ticket should have an assigned technician User.');

        $this->assertNotEquals($previousDateTime, $ticket->getDateModified(), 'Tickets modified date should be different than it previously was set as.');

        $this->assertEquals('Ticket Updated', $ticket->getTicketHistory()[1]->getSubject(), 'Second ticket history item should be \'Ticket Updated\'.');
    }

    public function testCloseTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->assertNull($ticket->getClosedDate(), 'Ticket should not be closed.');

        $this->ticketService->closeTicket($ticket, $this->testTechnicianUser);

        $this->assertNotNull($ticket->getClosedDate(), 'Ticket should have a closed date set.');

        $this->assertEquals('testTechnician', $ticket->getClosedBy()->getUsername(), 'Ticket should have a closed by User set.');

        $this->assertEquals('Ticket Closed', $ticket->getTicketHistory()[1]->getSubject(), 'Second ticket history item should be \'Ticket Closed\'.');

        $this->assertEquals('Closed', $ticket->getStatus()->getFriendlyName(), 'Status should be \'Closed\'.');
    }

    public function testCloseTicketsByEntity(): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $this->ticketService->createTicket($this->testTicketData);
        }

        $tickets = $this->entityManager->getRepository(Ticket::class)->findAll();

        $ticketsByEntity = $this->ticketService->closeTickets($tickets, $this->testTechnicianUser);

        foreach ($ticketsByEntity as $ticket) {
            $this->assertEquals('testTechnician', $ticket->getClosedBy()->getUsername(), 'Ticket closed by Ticket::class should have a closed by User set.');
        }
    }

    public function testCloseTicketsById(): void
    {
        for ($i = 0; $i < 5; ++$i) {
            $this->ticketService->createTicket($this->testTicketData);
        }

        $tickets = $this->entityManager->getRepository(Ticket::class)->findAll();

        $ids = [];

        foreach ($tickets as $ticket) {
            $ids[] = $ticket->getId();
        }

        $this->ticketService->closeTickets($ids, $this->testTechnicianUser);

        $ticketsById = $this->ticketService->getTickets();

        foreach ($ticketsById as $ticket) {
            $this->assertEquals('testTechnician', $ticket->getClosedBy()->getUsername(), 'Ticket closed by id should have a closed by User set.');
            $this->assertInstanceOf(Ticket::class, $ticket);
        }
    }

    public function testDeleteTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->assertCount(1, $this->entityManager->getRepository(Ticket::class)->findAll(), 'Ticket count should be 1.');

        $this->ticketService->deleteTicket($ticket);

        $this->assertCount(0, $this->entityManager->getRepository(Ticket::class)->findAll(), 'Ticket count should 0.');
    }

    public function testResolvedTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->assertNull($ticket->getResolvedDate(), 'Ticket should not have a resolved date set.');

        $this->assertNull($ticket->getResolvedBy(), 'Ticket should not have a resolved by User.');

        $this->ticketService->resolveTicket($ticket, $this->testTechnicianUser);

        $this->assertInstanceOf(\DateTimeImmutable::class, $ticket->getResolvedDate(), 'Ticket should have a resolved date set.');

        $this->assertEquals('testTechnician', $ticket->getResolvedBy()->getUsername(), 'Ticket should have a resolved by User set.');

        $this->assertEquals('Ticket Resolved', $ticket->getTicketHistory()[1]->getSubject(), 'Second ticket history item should be \'Ticket Resolved\'.');

        $this->assertEquals('Resolved', $ticket->getStatus()->getFriendlyName(), 'Status should be \'Resolved\'.');
    }

    public function testCancelledTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $ticket = $this->ticketService->cancelTicket($ticket, $this->testTechnicianUser);

        $this->assertEquals('testTechnician', $ticket->getCancelledBy()->getUsername(), 'Ticket cancelled by User set.');
    }

    public function testGetTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->assertInstanceOf(Ticket::class, $this->ticketService->getTicket($ticket), 'Ticket found by Ticket::class should be an instance of Ticket');

        $this->assertInstanceof(Ticket::class, $this->ticketService->getTicket($ticket->getId()), 'Ticket found by ID should be an instance of Ticket');
    }

    public function testFailGetTicket(): void
    {
        $this->ticketService->createTicket($this->testTicketData);

        $this->expectException(\InvalidArgumentException::class);

        $this->ticketService->getTicket(null, []);
    }

    public function testGetTicketWhereClosed(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->ticketService->closeTicket($ticket, $this->testTechnicianUser);

        $this->assertInstanceOf(Ticket::class, $this->ticketService->getTicket(filter: ['closedBy' => $this->testTechnicianUser]));
    }

    public function testGetTicketWhereResolved(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->ticketService->resolveTicket($ticket, $this->testTechnicianUser);

        $this->assertInstanceOf(Ticket::class, $this->ticketService->getTicket(filter: ['resolvedBy' => $this->testTechnicianUser]));
    }

    public function testGetTickets(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->ticketService->createTicket($this->testTicketData);
        }

        $this->assertCount(10, $this->ticketService->getTickets(), 'Ticket count should be 10.');
    }

    public function testGetTicketsWhereClosed(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->ticketService->createTicket($this->testTicketData);
        }

        $tickets = $this->entityManager->getRepository(Ticket::class)->findBy([], limit: 5);

        $this->ticketService->closeTickets($tickets, $this->testTechnicianUser);

        $this->assertCount(5, $this->ticketService->getTickets(['closedBy' => $this->testTechnicianUser]), 'Ticket count should be 5.');
    }

    public function testGetTicketsWhereResolved(): void
    {
        for ($i = 0; $i < 10; ++$i) {
            $this->ticketService->createTicket($this->testTicketData);
        }

        $tickets = $this->entityManager->getRepository(Ticket::class)->findBy([], limit: 5);

        $this->ticketService->resolveTickets($tickets, $this->testTechnicianUser);

        $this->assertCount(5, $this->ticketService->getTickets(['resolvedBy' => $this->testTechnicianUser]), 'Ticket count should be 5.');
    }

    public function testChangeStatus(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        /** @var Status $closedStatus */
        $closedStatus = $this->statusRepository->findOneBy(['friendlyName' => 'Closed']);

        $this->ticketService->changeStatus($ticket, $closedStatus, $this->testTechnicianUser);

        $this->assertEquals('Closed', $ticket->getStatus()->getFriendlyName(), 'Ticket status should be Closed.');
    }
}
