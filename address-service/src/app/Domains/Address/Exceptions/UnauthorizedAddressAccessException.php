<?php

namespace App\Domains\Address\Exceptions;

use Exception;

class UnauthorizedAddressAccessException extends Exception
{
    protected $message = 'Unauthorized to access this address';
    protected $code = 403;
}
