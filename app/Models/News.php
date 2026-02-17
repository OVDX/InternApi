<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'News',
    title: 'News',
    description: 'Модель новини',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Нова технологія'),
        new OA\Property(property: 'short_description', type: 'string', example: 'Короткий опис'),
        new OA\Property(property: 'image', type: 'string', example: 'http://localhost:8000/storage/news/image.jpg', nullable: true),
        new OA\Property(property: 'is_published', type: 'boolean', example: true),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'user',
            ref: '#/components/schemas/User',
            description: 'Автор новини'
        ),
        new OA\Property(
            property: 'content_blocks',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ContentBlock')
        ),
    ]
)]
class News extends Model
{
    protected $fillable = [
        'user_id', 'title', 'image', 'short_description',
        'is_published', 'published_at'
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contentBlocks()
    {
        return $this->hasMany(ContentBlock::class)->orderBy('order');
    }


    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
