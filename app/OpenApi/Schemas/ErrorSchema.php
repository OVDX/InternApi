<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'ErrorResponse')]
class ErrorSchema
{
    #[OA\Property(property: 'status', type: 'string', example: 'error')]
    public string $status;

    #[OA\Property(property: 'message', type: 'string', example: 'Не знайдено')]
    public string $message;
}

#[OA\Schema(schema: 'UnauthenticatedResponse')]
class UnauthenticatedSchema
{
    #[OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.')]
    public string $message;
}

#[OA\Schema(schema: 'DeletedResponse')]
class DeletedSchema
{
    #[OA\Property(property: 'status', type: 'string', example: 'success')]
    public string $status;

    #[OA\Property(
        property: 'data',
        properties: [
            new OA\Property(property: 'message', type: 'string', example: 'Новину видалено')
        ],
        type: 'object'
    )]
    public mixed $data;
}

#[OA\Schema(schema: 'SuccessNewsResponse')]
class SuccessNewsSchema
{
    #[OA\Property(property: 'status', type: 'string', example: 'success')]
    public string $status;

    #[OA\Property(
        property: 'data',
        ref: '#/components/schemas/NewsResource'
    )]
    public mixed $data;
}

#[OA\Schema(schema: 'NewsListResponse')]
class NewsListSchema
{
    #[OA\Property(property: 'status', type: 'string', example: 'success')]
    public string $status;

    #[OA\Property(
        property: 'data',
        properties: [
            new OA\Property(property: 'current_page', type: 'integer', example: 1),
            new OA\Property(
                property: 'data',
                type: 'array',
                items: new OA\Items(ref: '#/components/schemas/NewsResource')
            ),
            new OA\Property(property: 'total', type: 'integer', example: 50),
            new OA\Property(property: 'per_page', type: 'integer', example: 15),
        ],
        type: 'object'
    )]
    public mixed $data;
}
