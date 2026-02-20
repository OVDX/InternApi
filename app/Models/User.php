<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Attributes as OA;
use Spatie\Permission\Traits\HasRoles;

#[OA\Schema(
    schema: 'User',
    title: 'User',
    description: 'Модель користувача',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Іван Петренко'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com'),
        new OA\Property(property: 'bio', type: 'string', example: 'Опис користувача', nullable: true),
        new OA\Property(property: 'avatar', type: 'string', example: 'avatars/avatar.jpg', nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class User extends Authenticatable
{
    use HasApiTokens, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'avatar', 'bio'];
    protected $hidden = ['password', 'remember_token'];

    public function news()
    {
        return $this->hasMany(News::class);
    }
}
