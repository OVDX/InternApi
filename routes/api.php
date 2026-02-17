<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NewsBlockController;
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
//        Route::prefix('{newsId}/blocks')->group(function () {
//            Route::post('/', [NewsBlockController::class, 'store']);
//            Route::post('/{blockId}', [NewsBlockController::class, 'update']);
//            Route::delete('/{blockId}', [NewsBlockController::class, 'destroy']);
//        });
    });
});
