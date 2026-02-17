<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenApiSpec;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\ContentBlock;
use App\Models\News;
use App\Services\NewsBlockService;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
        description: 'Створити нову новину з контентними блоками',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'short_description', 'is_published'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Нова технологія'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'short_description', type: 'string', example: 'Опис'),
                        new OA\Property(property: 'is_published', type: 'boolean', example: true),


                        new OA\Property(
                            property: 'content_blocks[0][type]',
                            type: 'string',
                            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
                            example: 'text',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[0][order]',
                            type: 'integer',
                            example: 1,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[0][text_content]',
                            type: 'string',
                            example: 'Оновлений текст блоку',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[0][image]',
                            description: 'Нове зображення для блоку',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),

                        // Другий блок (створення нового)
                        new OA\Property(
                            property: 'content_blocks[1][type]',
                            type: 'string',
                            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
                            example: 'image',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[1][order]',
                            type: 'integer',
                            example: 2,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[1][text_content]',
                            type: 'string',
                            example: 'Оновлений текст блоку',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[1][image]',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),

                        new OA\Property(
                            property: 'content_blocks[2][type]',
                            type: 'string',
                            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
                            example: 'text_image_right',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[2][order]',
                            type: 'integer',
                            example: 3,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[2][text_content]',
                            type: 'string',
                            example: 'Текст з картинкою',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[2][image]',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_NEWS],
        responses: [
            new OA\Response(response: 201, description: 'Новину створено'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]

    public function store(StoreNewsRequest $request)
    {
        DB::beginTransaction();

        try {
            // Створення новини
            $news = News::create([
                'user_id' => $request->user()->id,
                'title' => $request->title,
                'image' => $request->hasFile('image')
                    ? $request->file('image')->store('news', 'public')
                    : null,
                'short_description' => $request->short_description,
                'is_published' => $request->boolean('is_published'),
                'published_at' => $request->boolean('is_published') ? now() : null,
            ]);

            // Створення блоків, якщо є
            if ($request->has('content_blocks')) {
                $blocks = $request->input('content_blocks');

                // АВТОМАТИЧНА ПЕРЕНУМЕРАЦІЯ: Сортуємо по order
                usort($blocks, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

                // Створюємо блоки з послідовною нумерацією
                foreach ($blocks as $index => $blockData) {
                    $imageUrl = null;

                    if ($request->hasFile("content_blocks.{$index}.image")) {
                        $imageUrl = $request->file("content_blocks.{$index}.image")
                            ->store('content_blocks', 'public');
                    }

                    $news->contentBlocks()->create([
                        'type' => $blockData['type'],
                        'text_content' => $blockData['text_content'] ?? null,
                        'image_url' => $imageUrl,
                        'order' => $index + 1, // ← Автоматично: 1, 2, 3, 4...
                    ]);
                }
            }

            DB::commit();

            return $this->successResponse(
                NewsResource::make($news->load(['contentBlocks', 'user'])),
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
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
        description: 'Оновити новину з контентними блоками. Блоки, які не передані в запиті, будуть видалені',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Оновлена назва', nullable: true),
                        new OA\Property(property: 'image', description: 'Нове головне зображення', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'short_description', type: 'string', example: 'Оновлений опис', nullable: true),

                        // Перший блок (оновлення існуючого)
                        new OA\Property(
                            property: 'content_blocks[0][id]',
                            description: 'ID існуючого блоку для оновлення (якщо немає - створюється новий)',
                            type: 'integer',
                            example: 5,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[0][type]',
                            type: 'string',
                            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
                            example: 'text',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[0][order]',
                            type: 'integer',
                            example: 1,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[0][text_content]',
                            type: 'string',
                            example: 'Оновлений текст блоку',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[0][image]',
                            description: 'Нове зображення для блоку',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),

                        new OA\Property(
                            property: 'content_blocks[1][id]',
                            description: 'ID існуючого блоку для оновлення (якщо немає - створюється новий)',
                            type: 'integer',
                            example: 5,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[1][type]',
                            type: 'string',
                            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
                            example: 'image',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[1][order]',
                            type: 'integer',
                            example: 2,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[1][text_content]',
                            type: 'string',
                            example: 'Оновлений текст блоку',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[1][image]',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),

                        new OA\Property(
                            property: 'content_blocks[2][id]',
                            type: 'integer',
                            example: 8,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[2][type]',
                            type: 'string',
                            enum: ['text', 'image', 'text_image_right', 'text_image_left'],
                            example: 'text_image_right',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[2][order]',
                            type: 'integer',
                            example: 3,
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[2][text_content]',
                            type: 'string',
                            example: 'Текст з картинкою',
                            nullable: true
                        ),
                        new OA\Property(
                            property: 'content_blocks[2][image]',
                            type: 'string',
                            format: 'binary',
                            nullable: true
                        ),
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
        DB::beginTransaction();

        try {
            $news = News::where('user_id', auth()->id())->findOrFail($id);

            $data = $request->only(['title', 'short_description']);

            if ($request->hasFile('image')) {
                if ($news->image) {
                    Storage::disk('public')->delete($news->image);
                }
                $data['image'] = $request->file('image')->store('news', 'public');
            }

            $news->update($data);

            if ($request->has('content_blocks')) {
                $blocks = $request->input('content_blocks');

                usort($blocks, fn($a, $b) => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

                $existingBlockIds = [];

                foreach ($blocks as $index => $blockData) {
                    $correctOrder = $index + 1;

                    if (isset($blockData['id'])) {
                        $block = $news->contentBlocks()->find($blockData['id']);

                        if ($block) {
                            $type = $blockData['type'];

                            $updateData = [
                                'type' => $type,
                                'order' => $correctOrder,
                            ];

                            if ($type === 'text') {
                                $updateData['text_content'] = $blockData['text_content'] ?? null;

                                if ($block->image_url) {
                                    Storage::disk('public')->delete($block->image_url);
                                }
                                $updateData['image_url'] = null;

                            } elseif ($type === 'image') {
                                $updateData['text_content'] = null;

                                if ($request->hasFile("content_blocks.{$index}.image")) {
                                    if ($block->image_url) {
                                        Storage::disk('public')->delete($block->image_url);
                                    }
                                    $updateData['image_url'] = $request->file("content_blocks.{$index}.image")
                                        ->store('content_blocks', 'public');
                                }

                            } elseif (in_array($type, ['text_image_right', 'text_image_left'])) {
                                $updateData['text_content'] = $blockData['text_content'] ?? null;

                                if ($request->hasFile("content_blocks.{$index}.image")) {
                                    if ($block->image_url) {
                                        Storage::disk('public')->delete($block->image_url);
                                    }
                                    $updateData['image_url'] = $request->file("content_blocks.{$index}.image")
                                        ->store('content_blocks', 'public');
                                }
                            }

                            $block->update($updateData);
                            $existingBlockIds[] = $block->id;
                        }
                    } else {
                        $type = $blockData['type'];
                        $imageUrl = null;

                        if ($request->hasFile("content_blocks.{$index}.image")) {
                            $imageUrl = $request->file("content_blocks.{$index}.image")
                                ->store('content_blocks', 'public');
                        }

                        $newBlock = $news->contentBlocks()->create([
                            'type' => $type,
                            'text_content' => in_array($type, ['text', 'text_image_right', 'text_image_left'])
                                ? ($blockData['text_content'] ?? null)
                                : null,
                            'image_url' => $imageUrl,
                            'order' => $correctOrder,
                        ]);

                        $existingBlockIds[] = $newBlock->id;
                    }
                }

                $blocksToDelete = $news->contentBlocks()->whereNotIn('id', $existingBlockIds)->get();

                foreach ($blocksToDelete as $block) {
                    if ($block->image_url) {
                        Storage::disk('public')->delete($block->image_url);
                    }
                    $block->delete();
                }
            }

            DB::commit();

            return $this->successResponse(
                NewsResource::make($news->load(['contentBlocks', 'user']))
            );

        } catch (ModelNotFoundException $e) {
            DB::rollBack();
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            DB::rollBack();
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
                if ($block->image_url) {
                    Storage::disk('public')->delete($block->image_url);
                }
            }

            if ($news->image) {
                Storage::disk('public')->delete($news->image);
            }

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
