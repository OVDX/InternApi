<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NewsResource',
    title: 'News Resource',
    description: 'News resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'title', type: 'string', example: 'Нова технологія в Laravel'),
        new OA\Property(property: 'image', type: 'string', example: 'http://localhost:8000/storage/news/image.png', nullable: true),
        new OA\Property(property: 'short_description', type: 'string', example: 'Короткий опис новини про нові можливості'),
        new OA\Property(property: 'is_published', type: 'boolean', example: true),
        new OA\Property(property: 'published_at', type: 'string', format: 'date-time', example: '2024-01-01 12:00:00', nullable: true),
        new OA\Property(
            property: 'author',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'Іван Петренко'),
                new OA\Property(property: 'avatar', type: 'string', example: 'http://localhost:8000/storage/avatars/avatar.png', nullable: true),
            ],
            type: 'object'
        ),
        new OA\Property(
            property: 'content_blocks',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ContentBlockResource')
        ),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01 12:00:00'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2024-01-01 12:00:00'),
    ],
    type: 'object'
)]
class NewsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'image' => getImageUrl($this->image),
            'short_description' => $this->short_description,
            'is_published' => $this->is_published,
            'published_at' => $this->published_at?->format('Y-m-d H:i:s'),
            'author' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'avatar' => getAvatarUrl($this->user->avatar),
            ],
            'content_blocks' => ContentBlockResource::collection($this->whenLoaded('contentBlocks')),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
