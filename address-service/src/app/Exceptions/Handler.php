<?php

namespace App\Exceptions;

use App\Domains\Address\Exceptions\AddressNotFoundException;
use App\Domains\Address\Exceptions\UnauthorizedAddressAccessException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register()
    {
        $this->renderable(function (AddressNotFoundException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        });

        $this->renderable(function (UnauthorizedAddressAccessException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        });
    }
}
