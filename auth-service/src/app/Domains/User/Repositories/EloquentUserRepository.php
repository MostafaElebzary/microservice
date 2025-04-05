<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;
use App\Domains\User\DTOs\UserData;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(UserData $userData): User
    {
        return User::create([
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => bcrypt($userData->password)
        ]);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
