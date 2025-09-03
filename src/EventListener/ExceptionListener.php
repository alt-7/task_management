<?php

declare(strict_types=1);

namespace App\EventListener;

use App\Exception\ValidationException;
use App\Http\ApiResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

readonly class ExceptionListener
{
    public function __construct(private LoggerInterface $logger) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : 500;

        $responseMessage = $statusCode === 500
            ? 'Internal server error'
            : $exception->getMessage();

        $details = [];

        if ($exception instanceof ValidationException) {
            $details = $exception->getDetails();
        }

        $this->logger->error($exception->getMessage(), [
            'exception' => $exception,
        ]);

        $event->setResponse(ApiResponse::error(
            $responseMessage,
            $statusCode,
            $details
        ));
    }
}
