<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Loan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Provides database access for Loan entities.
 *
 * @extends ServiceEntityRepository<Loan>
 */
class LoanRepository extends ServiceEntityRepository
{
    /**
     * Creates a repository for Loan entities.
     *
     * @param ManagerRegistry $registry
     *
     * @return void
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    /**
     * Returns active loans that are past their due date.
     *
     * @return list<Loan>
     */
    public function findOverdueLoans(): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.returnedAt IS NULL')
            ->andWhere('l.dueAt < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->orderBy('l.dueAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
