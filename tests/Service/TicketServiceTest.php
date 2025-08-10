<?php

namespace App\Tests\Service;

use App\Entity\Ticket;
use App\Entity\TicketHistory;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\TicketService;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TicketServiceTest extends KernelTestCase
{
    private TicketService $ticketService;
    private User $testRequestUser;
    private User $testTechnicianUser;
    private array $testTicketData;
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->entityManager = $container->get('doctrine')->getManager();

        $this->ticketService = $container->get(TicketService::class);

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
            'subject' => 'This is a test subject'
        ];
    }

    /**
     * @throws ORMException
     */
    protected function tearDown(): void
    {
        $this->truncateEntities([
            Ticket::class,
            TicketHistory::class,
        ]);
    }

    /**
     * @throws ORMException
     */
    private function truncateEntities(array $entities): void
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
    }

    public function testUpdateTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $previousDateTime = $ticket->getDateModified();

        $ticket->setAssignedUser($this->testTechnicianUser);

        $this->ticketService->updateTicket($ticket, $this->testTechnicianUser);

        $this->assertEquals('testTechnician', $ticket->getAssignedUser()->getUsername(), 'Ticket should have an assigned technician User.');

        $this->assertNotEquals($previousDateTime, $ticket->getDateModified(), 'Tickets modified date should be different than it previously was set as.');
    }

    public function testCloseTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->assertNull($ticket->getClosedDate(), 'Ticket should not be closed.');

        $this->ticketService->closeTicket($ticket, $this->testTechnicianUser);

        $this->assertNotNull($ticket->getClosedDate(), 'Ticket should have a closed date set.');

        $this->assertEquals('testTechnician', $ticket->getClosedBy()->getUsername(), 'Ticket should have a closed by User set.');
    }

    public function testDeleteTicket(): void
    {
        $ticket = $this->ticketService->createTicket($this->testTicketData);

        $this->assertCount(1, $this->entityManager->getRepository(Ticket::class)->findAll(), 'Ticket count should be 1.');

        $this->ticketService->deleteTicket($ticket);

        $this->assertCount(0, $this->entityManager->getRepository(Ticket::class)->findAll(), 'Ticket count should 0.');
    }
}
