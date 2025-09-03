<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ValidationException extends BadRequestHttpException
{
    public function __construct(string $message = 'Validation failed', array $details = [], \Throwable $previous = null)
    {
        parent::__construct($message, $previous, 0);
        $this->details = $details;
    }

    private array $details;

    public function getDetails(): array
    {
        return $this->details;
    }
}
