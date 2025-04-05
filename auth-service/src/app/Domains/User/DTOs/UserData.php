<?php

namespace App\Domains\User\DTOs;


use Illuminate\Http\Request;

class UserData
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password
    )
    {
    }


}
