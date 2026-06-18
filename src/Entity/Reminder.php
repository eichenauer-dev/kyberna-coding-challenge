<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ReminderRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Represents a stored reminder for an overdue loan.
 */
#[ORM\Entity(repositoryClass: ReminderRepository::class)]
#[ORM\Table(name: 'reminder')]
class Reminder
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Loan $loan = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $message = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Returns the unique identifier of the reminder.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the loan this reminder belongs to.
     *
     * @return Loan|null
     */
    public function getLoan(): ?Loan
    {
        return $this->loan;
    }

    /**
     * Sets the loan this reminder belongs to.
     *
     * @param Loan $loan
     *
     * @return $this
     */
    public function setLoan(Loan $loan): static
    {
        $this->loan = $loan;

        return $this;
    }

    /**
     * Returns the reminder message content.
     *
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * Sets the reminder message content.
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns when the reminder was created.
     *
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Sets when the reminder was created.
     *
     * @param \DateTimeImmutable $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
