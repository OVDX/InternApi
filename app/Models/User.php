<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'User',
    title: 'User',
    description: 'Модель користувача',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Іван Петренко'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'ivan@example.com'),
        new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Опис користувача'),
        new OA\Property(property: 'avatar', type: 'string', nullable: true, example: 'avatars/avatar.jpg'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = ['name', 'email', 'password', 'avatar', 'bio'];
    protected $hidden = ['password', 'remember_token'];

    public function news()
    {
        return $this->hasMany(News::class);
    }
}
