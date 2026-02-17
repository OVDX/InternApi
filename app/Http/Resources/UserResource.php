<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UserResource',
    title: 'User Resource',
    description: 'User resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Іван Петренко'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com'),
        new OA\Property(property: 'avatar_url', type: 'string', example: 'http://localhost:8000/storage/avatars/avatar.png', nullable: true),
        new OA\Property(property: 'bio', type: 'string', example: 'Короткий опис користувача', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01T12:00:00.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01T12:00:00.000000Z'),
    ],
    type: 'object'
)]
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar_url' => getFileUrl($this->avatar),
            'bio' => $this->bio,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
