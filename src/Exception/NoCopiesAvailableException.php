<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when a book has no available copies left to loan.
 */
final class NoCopiesAvailableException extends \RuntimeException
{
    /**
     * @param int $bookId
     *
     * @return void
     */
    public function __construct(int $bookId)
    {
        parent::__construct(sprintf('No copies available for book with id %d.', $bookId));
    }
}
