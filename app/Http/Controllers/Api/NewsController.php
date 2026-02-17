<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenApiSpec;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use App\Services\NewsBlockService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class NewsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private NewsBlockService $newsBlockService
    ) {}

    #[OA\Get(
        path: '/api/news',
        description: 'Отримати список власних новин користувача з пагінацією, пошуком та фільтрацією',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [
            new OA\QueryParameter(name: 'search', required: false, schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'is_published', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\QueryParameter(
                name: 'sort_by',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    enum: ['id', 'title', 'created_at', 'updated_at', 'published_at'],
                    default: 'created_at'
                )
            ),
            new OA\QueryParameter(
                name: 'sort_order',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'], default: 'desc')
            ),
            new OA\QueryParameter(name: 'page', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Список новин',
                content: new OA\JsonContent(ref: '#/components/schemas/NewsListResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')
            ),
        ]
    )]
    public function index(Request $request)
    {
        try {
            $query = News::with(['contentBlocks', 'user'])
                ->where('user_id', $request->user()->id);

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%")
                        ->orWhere('short_description', 'like', "%{$request->search}%");
                });
            }

            if ($request->has('is_published')) {
                $query->where('is_published', $request->boolean('is_published'));
            }

            $allowedSortFields = ['id', 'title', 'created_at', 'updated_at', 'published_at'];
            $sortBy = $request->get('sort_by', 'created_at');

            if (!in_array($sortBy, $allowedSortFields)) {
                $sortBy = 'created_at';
            }

            $sortOrder = $request->get('sort_order', 'desc');
            if (!in_array($sortOrder, ['asc', 'desc'])) {
                $sortOrder = 'desc';
            }

            $query->orderBy($sortBy, $sortOrder);

            return $this->successResponse($query->paginate(15));

        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при отриманні новин', 500, $e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/news',
        description: 'Створити нову новину',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'short_description', 'is_published'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Нова технологія змінює світ'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', description: 'Головне зображення новини'),
                        new OA\Property(property: 'short_description', type: 'string', example: 'Короткий опис про нову технологію'),
                        new OA\Property(property: 'is_published', type: 'string', enum: ['true', 'false'], example: 'true'),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_NEWS],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Новину створено',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessNewsResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreNewsRequest $request)
    {
        try {
            $news = News::create([
                'user_id'           => $request->user()->id,
                'title'             => $request->title,
                'image'             => $request->hasFile('image')
                    ? $request->file('image')->store('news', 'public')
                    : null,
                'short_description' => $request->short_description,
                'is_published'      => filter_var($request->is_published, FILTER_VALIDATE_BOOLEAN),
                'published_at'      => now(),
            ]);

            return $this->successResponse(NewsResource::make($news->load('user')), 201);

        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при створенні новини', 500, $e->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/news/{id}',
        description: 'Отримати деталі новини',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Деталі новини',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessNewsResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Не знайдено',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function show(int $id)
    {
        try {
            $news = News::with(['contentBlocks', 'user'])
                ->where('user_id', auth()->id())
                ->findOrFail($id);

            return $this->successResponse(NewsResource::make($news));

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при отриманні новини', 500, $e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/news/{id}',
        description: 'Оновити новину',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Оновлена назва', nullable: true),
                        new OA\Property(property: 'image', description: 'Нове зображення', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'short_description', type: 'string', example: 'Оновлений опис', nullable: true),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Новину оновлено',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessNewsResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Не знайдено',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateNewsRequest $request, int $id)
    {
        try {
            $news = News::where('user_id', auth()->id())->findOrFail($id);

            $data = [];

            if ($request->filled('title')) {
                $data['title'] = $request->title;
            }

            if ($request->filled('short_description')) {
                $data['short_description'] = $request->short_description;
            }

            if ($request->hasFile('image')) {
                if ($news->image) {
                    \Storage::disk('public')->delete($news->image);
                }
                $data['image'] = $request->file('image')->store('news', 'public');
            }

            $news->update($data);

            return $this->successResponse(NewsResource::make($news->load('contentBlocks', 'user')));

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при оновленні новини', 500, $e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/news/{id}',
        description: 'Видалити новину',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Новину видалено',
                content: new OA\JsonContent(ref: '#/components/schemas/DeletedResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Не знайдено',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function destroy(int $id)
    {
        try {
            $news = News::where('user_id', auth()->id())->findOrFail($id);
            $news->delete();

            return $this->successResponse(['message' => 'Новину видалено']);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при видаленні новини', 500, $e->getMessage());
        }
    }

    #[OA\Patch(
        path: '/api/news/{id}/toggle-status',
        description: 'Змінити статус публікації',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Статус змінено',
                content: new OA\JsonContent(ref: '#/components/schemas/SuccessNewsResponse')
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthorized',
                content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')
            ),
            new OA\Response(
                response: 404,
                description: 'Не знайдено',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
            ),
        ]
    )]
    public function toggleStatus(int $id)
    {
        try {
            $news = News::where('user_id', auth()->id())->findOrFail($id);
            $news->update(['is_published' => !$news->is_published]);

            return $this->successResponse(NewsResource::make($news));

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при зміні статусу', 500, $e->getMessage());
        }
    }
}
