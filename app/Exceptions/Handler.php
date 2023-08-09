<?php

namespace App\Exceptions;

use Exception;
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
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException $e, $request) {
            return response()->json([
                'error' => 'Unauthorized Error',
                'message' => 'You must be authenticated to access this content',
                'status'  => 401,
            ], 403);
        });

        $this->renderable(function (\Spatie\Permission\Exceptions\UnauthorizedException $e, $request) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You do not have the required authorization',
                'status'  => 403,
            ], 403);
        });

        $this->renderable(function (\Illuminate\Validation\ValidationException $e, $request) {
            //dd($e);
            return response()->json([
                'error' => $e->getMessage(),
                'message' => $e->errors(),
                'status'  => 422,
            ], 422);
        });

        $this->renderable(function (Exception $e, $request) {
            return response()->json([
                'error' => 'Internal server error',
                'message' => 'Something went wrong, please try again later',
                'status'  => 500,
            ], 500);
        });
    }
}
