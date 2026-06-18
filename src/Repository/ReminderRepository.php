<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Loan;
use App\Entity\Reminder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Provides database access for Reminder entities.
 *
 * @extends ServiceEntityRepository<Reminder>
 */
class ReminderRepository extends ServiceEntityRepository
{
    /**
     * Creates a repository for Reminder entities.
     *
     * @param ManagerRegistry $registry
     *
     * @return void
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reminder::class);
    }

    /**
     * Checks whether a reminder already exists for the given loan on the given date.
     *
     * @param Loan $loan
     * @param \DateTimeImmutable $date
     *
     * @return bool
     *
     * @throws \DateMalformedStringException
     */
    public function hasReminderForLoanOnDate(Loan $loan, \DateTimeImmutable $date): bool
    {
        $startOfDay = $date->setTime(0, 0);
        $startOfNextDay = $startOfDay->modify('+1 day');

        $count = (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.loan = :loan')
            ->andWhere('r.createdAt >= :startOfDay')
            ->andWhere('r.createdAt < :startOfNextDay')
            ->setParameter('loan', $loan)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('startOfNextDay', $startOfNextDay)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
