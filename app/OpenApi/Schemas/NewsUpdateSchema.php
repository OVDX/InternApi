<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'NewsUpdateRequest',
    properties: [
        new OA\Property(property: 'title', type: 'string', example: 'Оновлена назва', nullable: true),
        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
        new OA\Property(property: 'short_description', type: 'string', example: 'Оновлений опис', nullable: true),

        new OA\Property(property: 'content_blocks[0][id]', type: 'integer', example: 5, nullable: true),
        new OA\Property(
            property: 'content_blocks[0][type]',
            type: 'string',
            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
            example: 'text',
            nullable: true
        ),
        new OA\Property(property: 'content_blocks[0][order]', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'content_blocks[0][text_content]', type: 'string', example: 'Текст', nullable: true),
        new OA\Property(property: 'content_blocks[0][image]', type: 'string', format: 'binary', nullable: true),
        new OA\Property(property: 'content_blocks[1][id]', type: 'integer', example: 5, nullable: true),
        new OA\Property(
            property: 'content_blocks[1][type]',
            type: 'string',
            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
            example: 'text',
            nullable: true
        ),
        new OA\Property(property: 'content_blocks[1][order]', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'content_blocks[1][text_content]', type: 'string', example: 'Текст', nullable: true),
        new OA\Property(property: 'content_blocks[1][image]', type: 'string', format: 'binary', nullable: true),
        new OA\Property(property: 'content_blocks[2][id]', type: 'integer', example: 5, nullable: true),
        new OA\Property(
            property: 'content_blocks[2][type]',
            type: 'string',
            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
            example: 'text',
            nullable: true
        ),
        new OA\Property(property: 'content_blocks[2][order]', type: 'integer', example: 1, nullable: true),
        new OA\Property(property: 'content_blocks[2][text_content]', type: 'string', example: 'Текст', nullable: true),
        new OA\Property(property: 'content_blocks[2][image]', type: 'string', format: 'binary', nullable: true),


    ]
)]
class NewsUpdateSchema
{
}
