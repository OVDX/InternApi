<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API для керування новинами з авторизацією через Sanctum',
    title: 'News API'
)]

#[OA\Server(url: 'http://localhost:8000', description: 'Development Server')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Введіть токен отриманий після логіну/реєстрації',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]


#[OA\Schema(
    schema: 'ApiSuccessResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Операція успішна'),
        new OA\Property(property: 'data', type: 'object')
    ]
)]
#[OA\Schema(
    schema: 'NotFoundResponse',
    properties: [new OA\Property(property: 'message', type: 'string', example: 'Ресурс не знайдено')]
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Помилка валідації'),
        new OA\Property(property: 'errors', type: 'object')
    ]
)]
#[OA\Schema(
    schema: 'ForbiddenResponse',
    properties: [new OA\Property(property: 'message', type: 'string', example: 'Доступ заборонено')]
)]


class OpenApiSpec
{
    public const TAG_AUTH = 'Authentication';
    public const TAG_NEWS = 'News Management';
    public const TAG_PUBLIC_NEWS = 'Public News';
    public const TAG_PROFILE = 'User Profile';
    public const TAG_ADMIN_USERS = 'Admin Users';  // ✅ Додай для тегів
}
