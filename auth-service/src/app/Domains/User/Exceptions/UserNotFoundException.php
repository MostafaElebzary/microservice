<?php

namespace App\Domains\User\Exceptions;

use Exception;

class UserNotFoundException extends Exception
{
    protected $message = 'User not found';
    protected $code = 404;
}
