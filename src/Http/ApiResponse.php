<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public static function success(string $message, mixed $data = null, int $status = 200): JsonResponse
    {
        $payload = ['message' => $message];
        if ($data !== null) {
            $payload['data'] = $data;
        }
        return new JsonResponse($payload, $status);
    }

    public static function error(string $message, int $code, array $details = []): JsonResponse
    {
        $payload = ['error' => ['code' => $code, 'message' => $message]];
        if ($details !== []){
            $payload['error']['details'] = $details;
        }
        return new JsonResponse($payload, $code);
    }
}
