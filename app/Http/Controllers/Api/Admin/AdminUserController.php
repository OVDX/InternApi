<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AdminUserController extends Controller
{
    use ApiResponse;


    #[OA\Get(
        path: '/api/admin/users',
        description: 'Отримати всіх користувачів (тільки для admin)',
        security: [['sanctum' => []]],
        tags: ['Admin Users'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список користувачів з ролями',
                content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Не авторизовано',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')
            ),
            new OA\Response(
                response: 403,
                description: 'Недостатньо прав (потрібна роль admin)',
                content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')
            ),
            new OA\Response(
                response: 500,
                description: 'Помилка сервера',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]

    public function index()
    {
        $users = User::with('roles')->paginate(15);

        return $this->successResponse(UserResource::collection($users));
    }

    #[OA\Get(
        path: '/api/admin/users/{id}',
        description: 'Деталі користувача (тільки для admin)',
        security: [['sanctum' => []]],
        tags: ['Admin Users'],
        parameters: [new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Деталі користувача', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
            new OA\Response(response: 404, description: 'Користувача не знайдено', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
            new OA\Response(response: 401, description: 'Не авторизовано', content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')),
            new OA\Response(response: 403, description: 'Недостатньо прав', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
            new OA\Response(response: 500, description: 'Помилка сервера', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]

    public function show(int $id)
    {
        $user = User::with('roles')->find($id);

        if (!$user) {
            return $this->errorResponse('Користувача не знайдено', 404);
        }


        return $this->successResponse(UserResource::make($user));
    }

    #[OA\Patch(
        path: '/api/admin/users/{id}/role',
        description: 'Змінити роль користувача (тільки для admin)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'application/json',
                schema: new OA\Schema(
                    required: ['role'],
                    properties: [
                        new OA\Property(
                            property: 'role',
                            type: 'string',
                            enum: ['user', 'admin'],
                            example: 'admin'
                        )
                    ]
                )
            )
        ),
        tags: ['Admin Users'],
        parameters: [new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
        new OA\Response(response: 200, description: 'Роль змінена', content: new OA\JsonContent(ref: '#/components/schemas/ApiSuccessResponse')),
        new OA\Response(response: 404, description: 'Користувача не знайдено', content: new OA\JsonContent(ref: '#/components/schemas/NotFoundResponse')),
        new OA\Response(response: 422, description: 'Помилка валідації', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
        new OA\Response(response: 401, description: 'Не авторизовано', content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')),
        new OA\Response(response: 403, description: 'Недостатньо прав', content: new OA\JsonContent(ref: '#/components/schemas/ForbiddenResponse')),
        new OA\Response(response: 500, description: 'Помилка сервера', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
    ]
    )]
    public function updateRole(UpdateUserRoleRequest $request, int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->errorResponse('Користувача не знайдено', 404);
        }

        $user->assignRole($request->role);

        $user->load('roles');

        return $this->successResponse([
            'message' => 'Роль користувача змінена',
            'user' => UserResource::make($user)
        ]);
    }
}
