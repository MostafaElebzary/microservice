<?php

namespace App\Http\Controllers;

use App\Domains\Address\Exceptions\UnauthorizedAddressAccessException;
use App\Domains\User\Exceptions\UnauthorizedException;
use App\Domains\User\Services\AuthService;
use App\Domains\User\DTOs\UserData;
use App\Domains\User\Services\RabbitMQService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Exception;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService ,  private RabbitMQService $rabbitmq)
    {
    }

    public function register(RegisterRequest $request)
    {
        $userData = new UserData(
            name: $request->name,
            email: $request->email,
            password: $request->password
        );

        $user = $this->authService->register($userData);

        return response()->json([
            'user' => new UserResource($user),
            'token' => $user->createToken('auth_token')->plainTextToken
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $token = $this->authService->login(
            email: $request->email,
            password: $request->password
        );

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }


    public function me()
    {
        $user = $this->authService->getAuthenticatedUser();
        return new UserResource($user);
    }

    public function UserAddresses()
    {
        $user = $this->authService->getAuthenticatedUser();
        try {
            $response = $this->rabbitmq->rpcRequest(
                queue: 'user_address_queue',
                payload: [
                    'id' => $user->id,
                    'service' => 'auth' // Identify calling service
                ],
                timeout: 3 // Fail fast
            );

            if (empty($response)) {
                throw new Exception('Empty response from Address service');
            }

            return [

                'data' => $response
            ];

        } catch (Exception $e) {
            Log::error("user address data failed: " . $e->getMessage());
            throw new UnauthorizedException('Address service unavailable');
        }

    }
}
