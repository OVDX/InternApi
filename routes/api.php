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

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('permissions:users.profile.update')->group(function () {
        Route::get('/profile', [UserController::class, 'show']);
        Route::post('/profile', [UserController::class, 'update']);
        Route::post('/profile/avatar', [UserController::class, 'uploadAvatar']);
        Route::delete('/profile/avatar', [UserController::class, 'deleteAvatar']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    Route::prefix('news')->group(function () {
        Route::middleware('permissions:news.view')->group(function () {
            Route::get('/', [NewsController::class, 'index']);
            Route::get('/{id}', [NewsController::class, 'show']);
        });

        Route::middleware('permissions:news.create')->post('/', [NewsController::class, 'store']);

        Route::middleware('permissions:news.update')->group(function () {
            Route::post('/{id}', [NewsController::class, 'update']);
            Route::patch('/{id}/toggle-status', [NewsController::class, 'toggleStatus']);
        });

        Route::middleware('permissions:news.delete')->delete('/{id}', [NewsController::class, 'destroy']);
    });

    Route::middleware('permissions:users.admin.manage')->prefix('admin')->group(function () {
        Route::apiResource('users', AdminUserController::class)->only(['index', 'show']);
        Route::patch('users/{user}/role', [AdminUserController::class, 'updateRole']);
    });
});

Route::fallback(fn() => response()->json(['message' => 'Endpoint не знайдено'], 404));
