<?php


namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ContentBlockData')]
class ContentBlockSchema
{
    #[OA\Property(property: 'id', type: 'integer', example: 1)]
    public int $id;

    #[OA\Property(property: 'news_id', type: 'integer', example: 1)]
    public int $news_id;

    #[OA\Property(property: 'type', type: 'string', enum: ['text', 'image', 'text_image_right', 'text_image_left'], example: 'text')]
    public string $type;

    #[OA\Property(property: 'text_content', type: 'string', nullable: true, example: 'Текст блоку')]
    public ?string $text_content;

    #[OA\Property(property: 'image_url', type: 'string', nullable: true, example: 'http://localhost:8000/storage/content_blocks/image.jpg')]
    public ?string $image_url;

    #[OA\Property(property: 'order', type: 'integer', example: 1)]
    public int $order;

    #[OA\Property(property: 'created_at', type: 'string', format: 'date-time')]
    public string $created_at;

    #[OA\Property(property: 'updated_at', type: 'string', format: 'date-time')]
    public string $updated_at;
}
