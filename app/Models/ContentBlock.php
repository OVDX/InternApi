<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContentBlock',
    title: 'Content Block',
    description: 'Контентний блок новини',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(
            property: 'type',
            type: 'string',
            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
            example: 'text'
        ),
        new OA\Property(property: 'text_content', type: 'string', example: 'Текст блоку', nullable: true),
        new OA\Property(property: 'image_url', type: 'string', example: 'http://localhost:8000/storage/blocks/image.jpg', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
class ContentBlock extends Model
{
    protected $fillable = ['news_id', 'type', 'text_content', 'image_url', 'order'];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
