<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when a requested loan does not exist.
 */
final class LoanNotFoundException extends \RuntimeException
{
    /**
     * @param int $loanId
     *
     * @return void
     */
    public function __construct(int $loanId)
    {
        parent::__construct(sprintf('Loan with id %d was not found.', $loanId));
    }
}
