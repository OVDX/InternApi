<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {

        });
    }


    public function render($request, Throwable $e)
    {
        if ($request->expectsJson() || str_starts_with($request->path(), 'api/')) {

            if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'Ресурс не знайдено'
                ], 404);
            }

            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => 'Помилка валідації',
                    'errors' => $e->errors()
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Не авторизовано'
                ], 401);
            }

            if ($e instanceof UnauthorizedException) {
                return response()->json([
                    'message' => 'Недостатньо прав'
                ], 403);
            }

            if ($e instanceof \Exception) {
                return response()->json([
                    'message' => 'Помилка сервера',
                    'error' => config('app.debug') ? $e->getMessage() : null
                ], 500);
            }
        }

        return parent::render($request, $e);
    }
}
