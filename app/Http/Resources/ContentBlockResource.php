<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ContentBlockResource',
    title: 'Content Block Resource',
    description: 'Content block resource representation',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(
            property: 'type',
            type: 'string',
            enum: ['text', 'image', 'text_image_left', 'text_image_right'],
            example: 'text'
        ),
        new OA\Property(property: 'text_content', type: 'string', example: 'Текст контентного блоку', nullable: true),
        new OA\Property(property: 'image_url', type: 'string', example: 'http://localhost:8000/storage/content_blocks/image.png', nullable: true),
        new OA\Property(property: 'order', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2024-01-01 12:00:00'),
    ],
    type: 'object'
)]
class ContentBlockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'text_content' => $this->text_content,
            'image_url' => getImageUrl($this->image_url),
            'order' => $this->order,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
