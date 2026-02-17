<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'News API',
    description: 'API для керування новинами з авторизацією через Sanctum'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Development Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    description: 'Введіть токен отриманий після логіну/реєстрації',
    bearerFormat: 'JWT',
    scheme: 'bearer'
)]
class OpenApiSpec
{
    public const TAG_AUTH = 'Authentication';
    public const TAG_NEWS = 'News Management';
    public const TAG_PUBLIC_NEWS = 'Public News';
    public const TAG_PROFILE = 'User Profile';
}
