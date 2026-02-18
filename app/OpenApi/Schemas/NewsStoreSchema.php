<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NewsStoreRequest',
    required: ['title', 'short_description', 'is_published'],
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Нова технологія'),
        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
        new OA\Property(property: 'short_description', type: 'string', example: 'Опис'),
        new OA\Property(property: 'is_published', type: 'boolean', example: true),

        new OA\Property(
            property: 'category_ids',
            description: 'Масив ID категорій (максимум 10)',
            type: 'array',
            items: new OA\Items(type: 'integer'),
            example: [1, 2, 3],
            nullable: true
        ),

        new OA\Property(
            property: 'content_blocks[0][type]',
            type: 'string',
            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
            example: 'text',
            nullable: true
        ),
        new OA\Property(property: 'content_blocks[0][order]', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'content_blocks[0][text_content]', type: 'string', example: 'Текст блоку', nullable: true),
        new OA\Property(property: 'content_blocks[0][image]', type: 'string', format: 'binary', nullable: true),
    ]
)]
class NewsStoreSchema
{
}
