<?php

namespace App\Domains\User\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    protected $message = 'Unauthorized';
    protected $code = 401;
}
