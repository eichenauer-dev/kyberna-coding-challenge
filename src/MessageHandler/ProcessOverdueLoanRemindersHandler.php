<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\Reminder;
use App\Message\ProcessOverdueLoanReminders;
use App\Repository\LoanRepository;
use App\Repository\ReminderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Creates reminder records for all overdue loans.
 */
#[AsMessageHandler]
final class ProcessOverdueLoanRemindersHandler
{
    /**
     * Creates a new ProcessOverdueLoanRemindersHandler instance.
     *
     * @param LoanRepository $loanRepository
     * @param ReminderRepository $reminderRepository
     * @param EntityManagerInterface $entityManager
     *
     * @return void
     */
    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly ReminderRepository $reminderRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Processes overdue loans and stores one reminder per loan per day.
     *
     * @param ProcessOverdueLoanReminders $message
     *
     * @return void
     *
     * @throws \DateMalformedStringException
     */
    public function __invoke(ProcessOverdueLoanReminders $message): void
    {
        $now = new \DateTimeImmutable();
        $createdReminders = 0;

        foreach ($this->loanRepository->findOverdueLoans() as $loan) {
            if ($this->reminderRepository->hasReminderForLoanOnDate($loan, $now)) {
                continue;
            }

            $member = $loan->getMember();
            $book = $loan->getBook();

            $reminder = (new Reminder())
                ->setLoan($loan)
                ->setMessage(sprintf(
                    'Reminder: loan #%d is overdue. Member "%s" should return "%s". Due date was %s.',
                    $loan->getId(),
                    $member?->getName() ?? 'unknown',
                    $book?->getTitle() ?? 'unknown',
                    $loan->getDueAt()?->format('Y-m-d H:i:s') ?? 'unknown',
                ))
                ->setCreatedAt($now);

            $this->entityManager->persist($reminder);
            ++$createdReminders;
        }

        if ($createdReminders > 0) {
            $this->entityManager->flush();
        }
    }
}
