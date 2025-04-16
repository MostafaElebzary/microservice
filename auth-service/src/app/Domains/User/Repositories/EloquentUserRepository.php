<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;
use App\Domains\User\DTOs\UserData;
use App\Jobs\UserRegistered;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function create(UserData $userData): User
    {
        $user = User::create([
            'name' => $userData->name,
            'email' => $userData->email,
            'password' => bcrypt($userData->password)
        ]);

        dispatch(new UserRegistered(['id' => $user->id, 'email' => $user->email]));

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}
