<?php

namespace App\Exceptions;

use App\Domains\User\Exceptions\UnauthorizedException;
use App\Domains\User\Exceptions\UserNotFoundException;
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
        $this->renderable(function (UserNotFoundException $e, $request) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        });

        $this->renderable(function (UnauthorizedException $e, $request) {
            return response()->json([
                'message' => $e->getMessage()
            ], $e->getCode());
        });
    }
}
