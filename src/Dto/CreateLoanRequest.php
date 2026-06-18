<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Request payload for creating a new book loan.
 */
final class CreateLoanRequest
{
    /**
     * Creates a new CreateLoanRequest instance.
     *
     * @param int|null $bookId The ID of the book to loan
     * @param int|null $memberId The ID of the member borrowing the book
     */
    public function __construct(
        #[Assert\NotNull(message: 'book_id is required.')]
        #[Assert\Positive(message: 'book_id must be a positive integer.')]
        public ?int $bookId = null,
        #[Assert\NotNull(message: 'member_id is required.')]
        #[Assert\Positive(message: 'member_id must be a positive integer.')]
        public ?int $memberId = null,
    ) {
    }
}
