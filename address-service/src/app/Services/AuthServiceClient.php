<?php

namespace App\Services;

use App\Domains\Address\Exceptions\UnauthorizedAddressAccessException;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthServiceClient
{
    public function __construct(
        private RabbitMQService $rabbitmq
    ) {}

    public function validateToken(string $token): array
    {
        try {
            $response = $this->rabbitmq->rpcRequest(
                queue: 'token_validation_queue',
                payload: [
                    'token' => $token,
                    'service' => 'address' // Identify calling service
                ],
                timeout: 3 // Fail fast
            );

            if (empty($response)) {
                throw new Exception('Empty response from auth service');
            }

            return [
                'valid' => $response['valid'] ?? false,
                'user' => $response['user'] ?? null
            ];

        } catch (Exception $e) {
            Log::error("Token validation failed: " . $e->getMessage());
            throw new UnauthorizedAddressAccessException('Authentication service unavailable');
        }
    }
}
