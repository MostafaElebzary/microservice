<?php

namespace App\Http\Controllers;

use App\Domains\User\Services\AuthService;
use App\Domains\User\DTOs\UserData;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use Madinastores\MadinastoresMicroserviceUtils\MadinastoresMicroserviceUtilsServiceProvider;
use Madinastores\MadinastoresMicroserviceUtils\RabbitMQUtils;
use Madinastores\MadinastoresMicroserviceUtils\Redis\RedisUtil;

class AuthController extends Controller
{
    public function __construct(private AuthService $authService)
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
}
