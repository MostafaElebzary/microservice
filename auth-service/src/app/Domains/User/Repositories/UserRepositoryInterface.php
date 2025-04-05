<?php

namespace App\Domains\User\Repositories;

use App\Domains\User\Models\User;
use App\Domains\User\DTOs\UserData;

interface UserRepositoryInterface
{
    public function create(UserData $userData): User;
    public function findByEmail(string $email): ?User;
}
