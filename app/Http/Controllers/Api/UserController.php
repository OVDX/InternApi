<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenApiSpec;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Http\Resources\UserResource;
use App\Services\AvatarService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AvatarService $avatarService
    ) {}

    #[OA\Get(
        path: '/api/profile',
        description: 'Отримати профіль поточного користувача',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_PROFILE],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Профіль користувача',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 500, description: 'Помилка сервера'),
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        return $this->successResponse(
            UserResource::make($request->user())
        );
    }

    #[OA\Post(
        path: '/api/profile',
        description: 'Оновити профіль користувача (використовуйте POST з _method=PUT)',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [

                        new OA\Property(property: 'name', type: 'string', example: 'Іван Оновлений'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'newemail@example.com'),
                        new OA\Property(property: 'bio', type: 'string', example: 'Новий опис профілю'),
                        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'newpassword123'),
                        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'newpassword123'),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_PROFILE],
        responses: [
            new OA\Response(response: 200, description: 'Профіль успішно оновлено', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Помилка сервера'),
        ]
    )]
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = [];

        if ($request->filled('name')) {
            $data['name'] = $request->input('name');
        }

        if ($request->filled('email')) {
            $data['email'] = $request->input('email');
        }

        if ($request->filled('bio')) {
            $data['bio'] = $request->input('bio');
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->input('password'));
        }

        $user->update($data);

        return $this->successResponse(
            UserResource::make($user->fresh())
        );
    }




    #[OA\Post(
        path: '/api/profile/avatar',
        description: 'Завантажити або оновити аватар користувача',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['avatar'],
                    properties: [
                        new OA\Property(
                            property: 'avatar',
                            description: 'Зображення аватара (jpg, png, max 2MB)',
                            type: 'string',
                            format: 'binary'
                        ),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_PROFILE],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Аватар успішно завантажено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'avatar', type: 'string', example: 'avatars/avatar123.jpg'),
                        new OA\Property(property: 'avatar_url', type: 'string', example: 'http://localhost:8000/storage/avatars/avatar123.jpg'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Помилка сервера'),
        ]
    )]
    public function uploadAvatar(UploadAvatarRequest $request): JsonResponse
    {
        $user = $request->user();

        $avatarData = $this->avatarService->upload(
            $request->file('avatar'),
            $user
        );

        return $this->successResponse($avatarData);
    }

    #[OA\Delete(
        path: '/api/profile/avatar',
        description: 'Видалити аватар користувача',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_PROFILE],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Аватар успішно видалено',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Аватар успішно видалено'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 404, description: 'Аватар не знайдено'),
        ]
    )]
    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->avatar) {
            return $this->notFoundResponse('У вас немає аватара');
        }

        $this->avatarService->delete($user);

        return $this->successResponse([
            'message' => 'Аватар успішно видалено'
        ]);
    }
}
