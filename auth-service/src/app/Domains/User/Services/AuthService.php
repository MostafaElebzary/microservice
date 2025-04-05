<?php

namespace App\Domains\User\Services;

use App\Domains\User\Exceptions\UnauthorizedException;
use app\Domains\User\Models\User;
use App\Domains\User\Repositories\UserRepositoryInterface;
use App\Domains\User\DTOs\UserData;
use App\Domains\User\Exceptions\UserNotFoundException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function register(UserData $userData)
    {
        return $this->userRepository->create($userData);
    }

    public function login(string $email, string $password)
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, $user->password)) {
            throw new UserNotFoundException('Invalid credentials');
        }

        return $user->createToken('auth_token')->plainTextToken;
    }

    public function getAuthenticatedUser(): User
    {
        if (!auth()->check()) {
            throw new UnauthorizedException();
        }

        return auth()->user();
    }
}
