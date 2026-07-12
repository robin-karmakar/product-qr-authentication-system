<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * Thrown by services/actions when a business rule is violated
 * (e.g. attempting to re-issue a QR code for an already-activated
 * unit, or confirming a custody transfer out of sequence).
 *
 * Caught centrally in bootstrap/app.php and rendered as a clean
 * JSON error envelope via ApiResponseTrait semantics.
 */
class ApiException extends Exception
{
    public function __construct(
        string $message,
        protected int $status = 422,
        protected mixed $errors = null,
    ) {
        parent::__construct($message);
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getErrors(): mixed
    {
        return $this->errors;
    }
}
