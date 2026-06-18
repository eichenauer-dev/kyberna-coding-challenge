<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Member;
use App\Exception\NoCopiesAvailableException;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use App\Service\LoanService;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for loan business logic.
 */
class LoanServiceTest extends TestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    private BookRepository&MockObject $bookRepository;

    private MemberRepository&MockObject $memberRepository;

    private LoanRepository $loanRepository;

    private LoanService $loanService;

    /**
     * Creates mocked dependencies and a LoanService instance for each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bookRepository = $this->createMock(BookRepository::class);
        $this->memberRepository = $this->createMock(MemberRepository::class);
        $this->loanRepository = $this->createStub(LoanRepository::class);

        $this->entityManager
            ->method('wrapInTransaction')
            ->willReturnCallback(static fn (callable $callback): mixed => $callback());

        $this->loanService = new LoanService(
            $this->entityManager,
            $this->bookRepository,
            $this->memberRepository,
            $this->loanRepository,
        );
    }

    /**
     * Ensures a successful loan decrements available copies and persists the loan.
     *
     * @return void
     *
     * @throws \DateMalformedStringException
     */
    public function testCreateLoanDecrementsAvailableCopiesAndPersistsLoan(): void
    {
        $book = (new Book())
            ->setTitle('1984')
            ->setAuthor('George Orwell')
            ->setIsbn('9780451524935')
            ->setAvailableCopies(2);

        $member = (new Member())
            ->setName('Alice Müller')
            ->setEmail('alice@example.com')
            ->setMembershipDate(new \DateTimeImmutable('2024-01-15'));

        $this->bookRepository
            ->expects($this->once())
            ->method('find')
            ->with(1, LockMode::PESSIMISTIC_WRITE)
            ->willReturn($book);

        $this->memberRepository
            ->expects($this->once())
            ->method('find')
            ->with(2)
            ->willReturn($member);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Loan::class));

        $loan = $this->loanService->createLoan(1, 2);

        $this->assertSame($book, $loan->getBook());
        $this->assertSame($member, $loan->getMember());
        $this->assertSame(1, $book->getAvailableCopies());
        $this->assertNotNull($loan->getLoanedAt());
        $this->assertNotNull($loan->getDueAt());
        $this->assertSame(
            $loan->getLoanedAt()->modify('+14 days')->format('Y-m-d'),
            $loan->getDueAt()->format('Y-m-d'),
        );
        $this->assertNull($loan->getReturnedAt());
    }

    /**
     * Ensures loan creation fails when the book has no available copies.
     *
     * @return void
     */
    public function testCreateLoanThrowsWhenNoCopiesAreAvailable(): void
    {
        $book = (new Book())
            ->setTitle('Dune')
            ->setAuthor('Frank Herbert')
            ->setIsbn('9780441172719')
            ->setAvailableCopies(0);

        $member = (new Member())
            ->setName('Bob Schmidt')
            ->setEmail('bob@example.com')
            ->setMembershipDate(new \DateTimeImmutable('2024-06-01'));

        $this->bookRepository
            ->expects($this->once())
            ->method('find')
            ->with(9, LockMode::PESSIMISTIC_WRITE)
            ->willReturn($book);

        $this->memberRepository
            ->expects($this->once())
            ->method('find')
            ->with(5)
            ->willReturn($member);

        $this->entityManager
            ->expects($this->never())
            ->method('persist');

        $this->expectException(NoCopiesAvailableException::class);
        $this->expectExceptionMessage('No copies available for book with id 9.');

        $this->loanService->createLoan(9, 5);
    }
}
