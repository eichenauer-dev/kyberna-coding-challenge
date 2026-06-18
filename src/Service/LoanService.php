<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Loan;
use App\Exception\BookNotFoundException;
use App\Exception\MemberNotFoundException;
use App\Exception\NoCopiesAvailableException;
use App\Repository\BookRepository;
use App\Repository\MemberRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handles business logic for creating and managing book loans.
 */
class LoanService
{
    private const int LOAN_PERIOD_DAYS = 14;

    /**
     * Initializes the loan service with required dependencies.
     *
     * @param EntityManagerInterface $entityManager
     * @param BookRepository $bookRepository
     * @param MemberRepository $memberRepository
     *
     * @return void
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BookRepository $bookRepository,
        private readonly MemberRepository $memberRepository,
    ) {
    }

    /**
     * Creates a new loan and decrements the book's available copies atomically.
     *
     * @param int $bookId
     * @param int $memberId
     *
     * @return Loan
     *
     * @throws BookNotFoundException
     * @throws MemberNotFoundException
     * @throws NoCopiesAvailableException
     */
    public function createLoan(int $bookId, int $memberId): Loan
    {
        return $this->entityManager->wrapInTransaction(function () use ($bookId, $memberId): Loan {
            $book = $this->bookRepository->find($bookId, LockMode::PESSIMISTIC_WRITE);
            if ($book === null) {
                throw new BookNotFoundException($bookId);
            }

            $member = $this->memberRepository->find($memberId);
            if ($member === null) {
                throw new MemberNotFoundException($memberId);
            }

            $availableCopies = $book->getAvailableCopies() ?? 0;
            if ($availableCopies < 1) {
                throw new NoCopiesAvailableException($bookId);
            }

            $book->setAvailableCopies($availableCopies - 1);

            $loanedAt = new \DateTimeImmutable();
            $loan = (new Loan())
                ->setBook($book)
                ->setMember($member)
                ->setLoanedAt($loanedAt)
                ->setDueAt($loanedAt->modify(sprintf('+%d days', self::LOAN_PERIOD_DAYS)));

            $this->entityManager->persist($loan);

            return $loan;
        });
    }
}
