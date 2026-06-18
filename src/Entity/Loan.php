<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a book loan between a member and a book.
 */
#[ORM\Entity]
#[ORM\Table(name: 'loan')]
class Loan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Book $book = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Member $member = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $loanedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dueAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $returnedAt = null;

    /**
     * Returns the unique identifier of the loan.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the borrowed book.
     *
     * @return Book|null
     */
    public function getBook(): ?Book
    {
        return $this->book;
    }

    /**
     * Sets the borrowed book.
     *
     * @param Book $book
     *
     * @return $this
     */
    public function setBook(Book $book): static
    {
        $this->book = $book;

        return $this;
    }

    /**
     * Returns the member who borrowed the book.
     *
     * @return Member|null
     */
    public function getMember(): ?Member
    {
        return $this->member;
    }

    /**
     * Sets the member who borrowed the book.
     *
     * @param Member $member
     *
     * @return $this
     */
    public function setMember(Member $member): static
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Returns the date and time when the book was loaned.
     *
     * @return \DateTimeImmutable|null
     */
    public function getLoanedAt(): ?\DateTimeImmutable
    {
        return $this->loanedAt;
    }

    /**
     * Sets the date and time when the book was loaned.
     *
     * @param \DateTimeImmutable $loanedAt
     *
     * @return $this
     */
    public function setLoanedAt(\DateTimeImmutable $loanedAt): static
    {
        $this->loanedAt = $loanedAt;

        return $this;
    }

    /**
     * Returns the due date and time for returning the book.
     *
     * @return \DateTimeImmutable|null
     */
    public function getDueAt(): ?\DateTimeImmutable
    {
        return $this->dueAt;
    }

    /**
     * Sets the due date and time for returning the book.
     *
     * @param \DateTimeImmutable $dueAt
     *
     * @return $this
     */
    public function setDueAt(\DateTimeImmutable $dueAt): static
    {
        $this->dueAt = $dueAt;

        return $this;
    }

    /**
     * Returns the date and time when the book was returned.
     *
     * @return \DateTimeImmutable|null
     */
    public function getReturnedAt(): ?\DateTimeImmutable
    {
        return $this->returnedAt;
    }

    /**
     * Sets the date and time when the book was returned.
     *
     * @param \DateTimeImmutable|null $returnedAt
     *
     * @return $this
     */
    public function setReturnedAt(?\DateTimeImmutable $returnedAt): static
    {
        $this->returnedAt = $returnedAt;

        return $this;
    }
}
