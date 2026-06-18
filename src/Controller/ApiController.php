<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\BookListQuery;
use App\Dto\CreateLoanRequest;
use App\Exception\BookNotFoundException;
use App\Exception\LoanAlreadyReturnedException;
use App\Exception\LoanNotFoundException;
use App\Exception\MemberNotFoundException;
use App\Exception\NoCopiesAvailableException;
use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use App\Repository\ReminderRepository;
use App\Service\LoanService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Provides JSON API endpoints for the book lending system.
 */
#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    /**
     * Initializes the API controller with required services.
     *
     * @param BookRepository $bookRepository
     * @param MemberRepository $memberRepository
     * @param LoanRepository $loanRepository
     * @param ReminderRepository $reminderRepository
     * @param LoanService $loanService
     *
     * @return void
     */
    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly MemberRepository $memberRepository,
        private readonly LoanRepository $loanRepository,
        private readonly ReminderRepository $reminderRepository,
        private readonly LoanService $loanService,
    ) {
    }

    /**
     * Returns a paginated list of books, optionally filtered by author.
     *
     * @param BookListQuery $query
     *
     * @return JsonResponse
     */
    #[Route('/books', name: 'books_list', methods: ['GET'])]
    public function books(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)] BookListQuery $query = new BookListQuery(),
    ): JsonResponse {
        $result = $this->bookRepository->findPaginatedByAuthor(
            $query->author,
            $query->page,
            $query->perPage,
        );

        return $this->json([
            'items' => $result['items'],
            'total' => $result['total'],
            'page' => $query->page,
            'per_page' => $query->perPage,
        ]);
    }

    /**
     * Returns a list of all library members.
     *
     * @return JsonResponse
     */
    #[Route('/members', name: 'members_list', methods: ['GET'])]
    public function members(): JsonResponse
    {
        return $this->json($this->memberRepository->findAll());
    }

    /**
     * Returns a list of all loans.
     *
     * @return JsonResponse
     */
    #[Route('/loans', name: 'loans_list', methods: ['GET'])]
    public function loans(): JsonResponse
    {
        return $this->json($this->loanRepository->findAll());
    }

    /**
     * Returns a list of all stored overdue loan reminders.
     *
     * @return JsonResponse
     */
    #[Route('/reminders', name: 'reminders_list', methods: ['GET'])]
    public function reminders(): JsonResponse
    {
        return $this->json($this->reminderRepository->findAll());
    }

    /**
     * Creates a new loan for the given book and member.
     *
     * @param CreateLoanRequest $request
     *
     * @return JsonResponse
     */
    #[Route('/loans', name: 'loans_create', methods: ['POST'])]
    public function createLoan(
        #[MapRequestPayload(validationFailedStatusCode: Response::HTTP_UNPROCESSABLE_ENTITY)] CreateLoanRequest $request,
    ): JsonResponse {
        try {
            $loan = $this->loanService->createLoan($request->bookId, $request->memberId);
        } catch (BookNotFoundException|MemberNotFoundException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (NoCopiesAvailableException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_CONFLICT);
        }

        return $this->json($loan, Response::HTTP_CREATED);
    }

    /**
     * Marks a loan as returned and restores the book's available copy count.
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    #[Route('/loans/{id}/return', name: 'loans_return', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function returnLoan(int $id): JsonResponse
    {
        try {
            $loan = $this->loanService->returnLoan($id);
        } catch (LoanNotFoundException|BookNotFoundException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (LoanAlreadyReturnedException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_CONFLICT);
        }

        return $this->json($loan);
    }
}
