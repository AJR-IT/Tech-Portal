<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function findByEntityArray(array $ticket): array
    {
        $qb = $this->createQueryBuilder('t');

        foreach ($ticket as $id) {
            $qb->orWhere('t.id = :ticket')
            ->setParameter('ticket', $id->getId());
        }

        return $qb->getQuery()
            ->getResult();
    }
}
