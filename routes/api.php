<?php

use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\PublicNewsController;
use Illuminate\Support\Facades\Route;

Route::prefix('public')->group(function () {
    Route::get('/news', [PublicNewsController::class, 'index']);
    Route::get('/news/{id}', [PublicNewsController::class, 'show']);
});
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('users', AdminUserController::class);
    Route::patch('users/{user}/role', [AdminUserController::class, 'updateRole']);
});
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::get('/profile', [UserController::class, 'show']);
    Route::post('/profile', [UserController::class, 'update']);
    Route::post('/profile/avatar', [UserController::class, 'uploadAvatar']);
    Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar']);

    Route::prefix('news')->group(function () {
        Route::patch('/{id}/toggle-status', [NewsController::class, 'toggleStatus']);
        Route::get('/', [NewsController::class, 'index']);
        Route::get('/{id}', [NewsController::class, 'show']);
        Route::post('/', [NewsController::class, 'store']);
        Route::post('/{id}', [NewsController::class, 'update']);
        Route::delete('/{id}', [NewsController::class, 'destroy']);

    });
});
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint не знайдено'
    ], 404);
});
