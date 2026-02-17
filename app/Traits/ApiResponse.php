<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function successResponse($data, int $status = 200): JsonResponse
    {
        return response()->json($data, $status);
    }

    protected function errorResponse(string $message, int $status = 500, ?string $error = null): JsonResponse
    {
        $response = ['message' => $message];

        if ($error && config('app.debug')) {
            $response['error'] = $error;
        }

        return response()->json($response, $status);
    }

    protected function validationErrorResponse(array $errors, string $message = 'Помилка валідації'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $errors
        ], 422);
    }

    protected function unauthorizedResponse(string $message = 'Невірні облікові дані'): JsonResponse
    {
        return $this->errorResponse($message, 401);
    }
    protected function notFoundResponse(string $message = 'Ресурс не знайдено'): JsonResponse
    {
        return response()->json(['message' => $message], 404);
    }
}
