<?php

namespace App\Http\Middleware;

use App\Services\AuthServiceClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithRabbitMQ
{
    public function __construct(
        private AuthServiceClient $authClient
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Token missing'], 401);
        }

        try {
            $userData = $this->authClient->validateToken($token);

            if (!$userData['valid']) {
                return response()->json(['message' => 'Invalid token'], 401);
            }

            // Attach user data to the request
            $request->merge([
                'user' => $userData['user']
            ]);

            return $next($request);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Authentication service unavailable',
                'error' => $e->getMessage()
            ], 503);
        }
    }
}
