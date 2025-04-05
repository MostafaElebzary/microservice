<?php

namespace App\Domains\Address\Exceptions;

use Exception;

class AddressNotFoundException extends Exception
{
    protected $message = 'Address not found';
    protected $code = 404;
}
