<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Query parameters for listing books with optional filtering and pagination.
 */
final class BookListQuery
{
    /**
     * Creates a new BookListQuery instance.
     *
     * @param string|null $author Optional author name filter
     * @param int $page The page number to return
     * @param int $perPage The number of books per page
     */
    public function __construct(
        public ?string $author = null,
        #[Assert\Positive(message: 'page must be a positive integer.')]
        public int $page = 1,
        #[Assert\Positive(message: 'per_page must be a positive integer.')]
        #[Assert\LessThanOrEqual(value: 100, message: 'per_page must not exceed 100.')]
        public int $perPage = 10,
    ) {
    }
}
