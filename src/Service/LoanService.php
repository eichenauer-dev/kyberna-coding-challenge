<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Loan;
use App\Exception\BookNotFoundException;
use App\Exception\LoanAlreadyReturnedException;
use App\Exception\LoanNotFoundException;
use App\Exception\MemberNotFoundException;
use App\Exception\NoCopiesAvailableException;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
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
     * @param LoanRepository $loanRepository
     *
     * @return void
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BookRepository $bookRepository,
        private readonly MemberRepository $memberRepository,
        private readonly LoanRepository $loanRepository,
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

    /**
     * Marks a loan as returned and increments the book's available copies atomically.
     *
     * @param int $loanId
     *
     * @return Loan
     *
     * @throws LoanNotFoundException
     * @throws LoanAlreadyReturnedException
     * @throws BookNotFoundException
     */
    public function returnLoan(int $loanId): Loan
    {
        return $this->entityManager->wrapInTransaction(function () use ($loanId): Loan {
            $loan = $this->loanRepository->find($loanId, LockMode::PESSIMISTIC_WRITE);
            if ($loan === null) {
                throw new LoanNotFoundException($loanId);
            }

            if ($loan->getReturnedAt() !== null) {
                throw new LoanAlreadyReturnedException($loanId);
            }

            $book = $loan->getBook();
            if ($book === null || $book->getId() === null) {
                throw new BookNotFoundException(0);
            }

            $bookId = $book->getId();
            $book = $this->bookRepository->find($bookId, LockMode::PESSIMISTIC_WRITE);
            if ($book === null) {
                throw new BookNotFoundException($bookId);
            }

            $loan->setReturnedAt(new \DateTimeImmutable());
            $book->setAvailableCopies(($book->getAvailableCopies() ?? 0) + 1);

            return $loan;
        });
    }
}
