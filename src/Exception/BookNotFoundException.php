<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when a requested book does not exist.
 */
final class BookNotFoundException extends \RuntimeException
{
    /**
     * @param int $bookId
     *
     * @return void
     */
    public function __construct(int $bookId)
    {
        parent::__construct(sprintf('Book with id %d was not found.', $bookId));
    }
}
