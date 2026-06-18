<?php

declare(strict_types=1);

namespace App\Exception;

/**
 * Thrown when a requested member does not exist.
 */
final class MemberNotFoundException extends \RuntimeException
{
    /**
     * @param int $memberId
     *
     * @return void
     */
    public function __construct(int $memberId)
    {
        parent::__construct(sprintf('Member with id %d was not found.', $memberId));
    }
}
