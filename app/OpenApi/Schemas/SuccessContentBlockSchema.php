<?php

namespace App\OpenApi\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(schema: 'SuccessContentBlockResponse')]
class SuccessContentBlockSchema
{
    #[OA\Property(property: 'status', type: 'string', example: 'success')]
    public string $status;

    #[OA\Property(property: 'data', ref: '#/components/schemas/ContentBlockData')]
    public ContentBlockSchema $data;
}
