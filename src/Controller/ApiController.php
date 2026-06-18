<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Provides JSON API endpoints for the book lending system.
 */
#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    /**
     * Initializes the API controller with book data access.
     *
     * @param BookRepository $bookRepository
     *
     * @return void
     */
    public function __construct(
        private readonly BookRepository $bookRepository,
    ) {
    }

    /**
     * Returns a list of all books in the library.
     *
     * @return JsonResponse
     */
    #[Route('/books', name: 'books_list', methods: ['GET'])]
    public function books(): JsonResponse
    {
        return $this->json($this->bookRepository->findAll());
    }
}
