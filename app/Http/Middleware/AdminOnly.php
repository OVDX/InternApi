<?php
// app/Http/Middleware/AdminOnly.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->hasRole('admin')) {
            if ($request->expectsJson()) {
                return new JsonResponse([
                    'message' => 'Доступ заборонено. Потрібна роль admin'
                ], 403);
            }
        }

        return $next($request);
    }
}
