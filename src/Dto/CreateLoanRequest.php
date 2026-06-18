<?php

declare(strict_types=1);

namespace App\Dto;

/**
 * Request payload for creating a new book loan.
 */
final class CreateLoanRequest
{
    /**
     * @param int $bookId The ID of the book to loan
     * @param int $memberId The ID of the member borrowing the book
     */
    public function __construct(
        public int $bookId,
        public int $memberId,
    ) {
    }
}
