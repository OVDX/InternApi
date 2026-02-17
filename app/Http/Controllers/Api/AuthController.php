<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenApiSpec;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AuthService $authService
    ) {}

    #[OA\Post(
        path: '/api/register',
        description: 'Реєстрація нового користувача',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'email', 'password', 'password_confirmation'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Іван Петренко'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'password123'),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_AUTH],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Користувача успішно створено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                        new OA\Property(property: 'token', type: 'string', example: '1|abcdef123456789'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Помилка валідації'),
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return $this->successResponse([
            'user' => UserResource::make($result['user']),
            'token' => $result['token']
        ], 201);
    }

    #[OA\Post(
        path: '/api/login',
        description: 'Вхід користувача в систему',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['email', 'password'],
                    properties: [
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_AUTH],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успішний вхід',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                        new OA\Property(property: 'token', type: 'string', example: '2|xyz789456123'),
                    ]
                )
            ),
            new OA\Response(response: 422, description: 'Невірні облікові дані'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        if (!$result) {
            return $this->unauthorizedResponse();
        }

        return $this->successResponse([
            'user' => UserResource::make($result['user']),
            'token' => $result['token']
        ]);
    }

    #[OA\Post(
        path: '/api/logout',
        description: 'Вихід з системи (видалення поточного токена)',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_AUTH],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успішний вихід',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Успішно вийшли з системи'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->successResponse([
            'message' => 'Успішно вийшли з системи'
        ]);
    }

    #[OA\Post(
        path: '/api/logout-all',
        description: 'Вихід з усіх пристроїв (видалення всіх токенів)',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_AUTH],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Успішний вихід з усіх пристроїв',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Успішно вийшли з усіх пристроїв'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function logoutAll(Request $request): JsonResponse
    {
        $this->authService->logoutAll($request->user());

        return $this->successResponse([
            'message' => 'Успішно вийшли з усіх пристроїв'
        ]);
    }
}
