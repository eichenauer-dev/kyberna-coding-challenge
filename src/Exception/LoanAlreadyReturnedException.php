<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when attempting to return a loan that is already marked as returned.
 */
final class LoanAlreadyReturnedException extends \RuntimeException
{
    /**
     * @param int $loanId
     *
     * @return void
     */
    public function __construct(int $loanId)
    {
        parent::__construct(sprintf('Loan with id %d has already been returned.', $loanId));
    }
}
