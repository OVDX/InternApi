<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenApiSpec;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use App\Services\NewsService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class NewsController extends Controller
{
    use ApiResponse;

    public function __construct(
        private NewsService $newsService

    ) {

    }

    #[OA\Get(
        path: '/api/news',
        description: 'Отримати список власних новин користувача з пагінацією, пошуком та фільтрацією',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],

        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                description: 'Мова для назв категорій',
                in: 'header',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'uk',
                    enum: ['uk', 'en', ]
                )
            ),
            new OA\QueryParameter(name: 'search', required: false, schema: new OA\Schema(type: 'string')),
            new OA\QueryParameter(name: 'is_published', required: false, schema: new OA\Schema(type: 'boolean')),
            new OA\QueryParameter(
                name: 'sort_by',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'created_at',
                    enum: ['id', 'title', 'created_at', 'updated_at', 'published_at']
                )
            ),
            new OA\QueryParameter(
                name: 'sort_order',
                required: false,
                schema: new OA\Schema(type: 'string', default: 'desc', enum: ['asc', 'desc'])
            ),
            new OA\QueryParameter(name: 'category_id', required: false, schema: new OA\Schema(description: 'ID категорії для фільтрації', type: 'integer')),
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
            $locale = $request->header('Accept-Language', app()->getLocale());
            $locale = in_array($locale, ['uk', 'en']) ? $locale : 'uk';

            $query = News::with([
                'contentBlocks',
                'user',
                'categories' => function($q) use ($locale) {
                    $q->withTranslation($locale);
                }
            ])
                ->where('user_id', $request->user()->id);

            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%")
                        ->orWhere('short_description', 'like', "%{$request->search}%");
                });
            }
            if ($request->filled('category_id')) {
                $query->whereHas('categories', function($q) use ($request) {
                    $q->where('id', $request->category_id);
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

            $news = $query->paginate(15);

            return $this->successResponse(NewsResource::collection($news));

        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при отриманні новин', 500, $e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/news',
        summary: 'Створити новину',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/NewsStoreRequest')
            )
        ),
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                description: 'Мова для назв категорій у відповіді',
                in: 'header',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'uk',
                    enum: ['uk', 'en', ]
                )
            )
        ],
        responses: [
            new OA\Response(response: 201, description: 'Created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreNewsRequest $request)
    {
        $uploadedFiles = [];
        DB::beginTransaction();

        try {
            $mainImage = null;
            if ($request->hasFile('image')) {
                $mainImage = $request->file('image')->store('news', 'public');
                $uploadedFiles[] = $mainImage;
            }

            $news = News::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'image' => $mainImage,
                'short_description' => $request->short_description,
                'is_published' => $request->boolean('is_published'),
                'published_at' => $request->boolean('is_published') ? now() : null,
            ]);

            if ($request->has('category_ids')) {
                if ($categoryIds = $this->parseCategoryIds($request)) {
                    $news->categories()->sync($categoryIds);
                }
            }

            if ($request->has('content_blocks')) {
                $this->newsService->processContentBlocks(
                    $news,
                    $request->input('content_blocks'),
                    $request,
                    $uploadedFiles
                );
            }

            DB::commit();

            $locale = $request->header('Accept-Language', app()->getLocale());
            $locale = in_array($locale, ['uk', 'en']) ? $locale : 'uk';

            $news->load([
                'contentBlocks',
                'user',
                'categories' => function($q) use ($locale) {
                    $q->withTranslation($locale);
                }
            ]);

            return $this->successResponse(
                NewsResource::make($news),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newsService->cleanupFiles($uploadedFiles);
            return $this->errorResponse('Помилка при створенні новини', 500, $e->getMessage());
        }
    }

    #[OA\Get(
        path: '/api/news/{id}',
        description: 'Отримати деталі новини',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                description: 'Мова для назв категорій',
                in: 'header',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'uk',
                    enum: ['uk', 'en', ]
                )
            ),
            new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
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
    public function show(Request $request, int $id)
    {
        try {
            $locale = $request->header('Accept-Language', app()->getLocale());
            $locale = in_array($locale, ['uk', 'en']) ? $locale : 'uk';

            $news = News::with([
                'contentBlocks',
                'user',
                'categories' => function($q) use ($locale) {
                    $q->withTranslation($locale);
                }
            ])
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
        summary: 'Оновити новину',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/NewsUpdateRequest')
            )
        ),
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [new OA\Parameter(
                name: 'Accept-Language',
                description: 'Мова для назв категорій у відповіді',
                in: 'header',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'uk',
                    enum: ['uk', 'en', ]
                )
            ),
            new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Updated'),
            new OA\Response(response: 404, description: 'Not Found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateNewsRequest $request, int $id)
    {
        $uploadedFiles = [];
        DB::beginTransaction();

        try {
            $news = News::where('user_id', auth()->id())->findOrFail($id);

            $data = $request->only(['title', 'short_description']);

            if ($request->hasFile('image')) {
                $oldImage = $news->image;
                $newImage = $request->file('image')->store('news', 'public');
                $uploadedFiles[] = $newImage;
                $data['image'] = $newImage;
                $this->newsService->deleteImage($oldImage);
            }

            $news->update($data);

            if ($request->has('category_ids')) {
                if ($categoryIds = $this->parseCategoryIds($request)) {
                    $news->categories()->sync($categoryIds);
                }
            }

            if ($request->has('content_blocks')) {
                $this->newsService->processContentBlocks(
                    $news,
                    $request->input('content_blocks'),
                    $request,
                    $uploadedFiles
                );
            }

            DB::commit();
            $locale = $request->header('Accept-Language', app()->getLocale());
            $locale = in_array($locale, ['uk', 'en']) ? $locale : 'uk';

            $news->load([
                'contentBlocks',
                'user',
                'categories' => function($q) use ($locale) {
                    $q->withTranslation($locale);
                }
            ]);

            return $this->successResponse(
                NewsResource::make($news)
            );

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            $this->newsService->cleanupFiles($uploadedFiles);
            return $this->notFoundResponse('Новину не знайдено');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->newsService->cleanupFiles($uploadedFiles);
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
        DB::beginTransaction();

        try {
            $news = News::where('user_id', auth()->id())->findOrFail($id);

            foreach ($news->contentBlocks as $block) {
                $this->newsService->deleteImage($block->image_url);
            }

            $this->newsService->deleteImage($news->image);

            $news->delete();
            DB::commit();

            return $this->successResponse(['message' => 'Новину видалено']);

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Помилка при видаленні новини', 500, $e->getMessage());
        }
    }

    #[OA\Patch(
        path: '/api/news/{id}/toggle-status',
        description: 'Змінити статус публікації',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [
            new OA\Parameter(
                name: 'Accept-Language',
                description: 'Мова для назв категорій',
                in: 'header',
                required: false,
                schema: new OA\Schema(
                    type: 'string',
                    default: 'uk',
                    enum: ['uk', 'en', ]
                )
            ),
            new OA\PathParameter(name: 'id', required: true, schema: new OA\Schema(type: 'integer'))],
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
    public function toggleStatus(Request $request, int $id)
    {
        try {
            $locale = $request->header('Accept-Language', app()->getLocale());
            $locale = in_array($locale, ['uk', 'en']) ? $locale : 'uk';

            $news = News::with([
                'contentBlocks',
                'user',
                'categories' => function($q) use ($locale) {
                    $q->withTranslation($locale);
                }
            ])
                ->where('user_id', auth()->id())
                ->findOrFail($id);
            $news->update(['is_published' => !$news->is_published]);

            return $this->successResponse(NewsResource::make($news));

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при зміні статусу', 500, $e->getMessage());
        }
    }
    private function parseCategoryIds($request): array
    {
        if (!$request->filled('category_ids')) {
            return [];
        }

        $categoryIds = $request->category_ids;

        if (is_string($categoryIds)) {
            return array_filter(
                array_map('intval', explode(',', $categoryIds)),
                fn($id) => $id > 0
            );
        }

        if (is_array($categoryIds)) {
            return array_filter($categoryIds, fn($id) => is_numeric($id) && $id > 0);
        }

        return [];
    }
}
