<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenApiSpec;
use App\Http\Requests\PublicNewsFilterRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use App\Services\PublicNewsQueryBuilder;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Attributes as OA;

class PublicNewsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PublicNewsQueryBuilder $queryBuilder
    ) {}

    #[OA\Get(
        path: '/api/public/news',
        description: 'Отримати список публічних новин без авторизації',
        security: [],
        tags: [OpenApiSpec::TAG_PUBLIC_NEWS],
        parameters: [
            new OA\QueryParameter(
                name: 'search',
                description: 'Пошук за назвою або описом',
                required: false,
                schema: new OA\Schema(type: 'string', example: 'технологія')
            ),
            new OA\QueryParameter(
                name: 'author_id',
                description: 'Фільтр за ID автора',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
            new OA\QueryParameter(
                name: 'date_from',
                description: 'Дата початку (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-01-01')
            ),
            new OA\QueryParameter(
                name: 'date_to',
                description: 'Дата кінця (YYYY-MM-DD)',
                required: false,
                schema: new OA\Schema(type: 'string', format: 'date', example: '2026-12-31')
            ),
            new OA\QueryParameter(
                name: 'page',
                description: 'Номер сторінки',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список публічних новин',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/NewsResource')
                        ),
                        new OA\Property(property: 'total', type: 'integer', example: 50),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Невалідні параметри запиту',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Помилка валідації'),
                        new OA\Property(property: 'errors', type: 'object')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Помилка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Помилка при отриманні новин')
                    ]
                )
            ),
        ]
    )]
    public function index(PublicNewsFilterRequest $request)
    {
        try {
            $query = $this->queryBuilder->build($request->validated());

            return $this->successResponse($query->paginate(15));

        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при отриманні новин', 500, $e->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/public/news/{id}',
        description: 'Отримати деталі однієї публічної новини',
        security: [],
        tags: [OpenApiSpec::TAG_PUBLIC_NEWS],
        parameters: [
            new OA\PathParameter(
                name: 'id',
                description: 'ID новини',
                required: true,
                schema: new OA\Schema(type: 'integer', example: 1)
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Деталі новини',
                content: new OA\JsonContent(ref: '#/components/schemas/NewsResource')
            ),
            new OA\Response(
                response: 404,
                description: 'Новину не знайдено або вона не опублікована',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Новину не знайдено або вона не опублікована')
                    ]
                )
            ),
            new OA\Response(
                response: 500,
                description: 'Помилка сервера',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Помилка при отриманні новини')
                    ]
                )
            ),
        ]
    )]
    public function show(int $id)
    {
        try {
            $news = News::with(['contentBlocks', 'user'])
                ->published()
                ->findOrFail($id);

            return $this->successResponse(NewsResource::make($news));

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину не знайдено або вона не опублікована');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при отриманні новини', 500, $e->getMessage());
        }
    }
}
