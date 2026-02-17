<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\OpenApiSpec;
use App\Http\Requests\StoreNewsBlockRequest;
use App\Http\Requests\UpdateNewsBlockRequest;
use App\Models\News;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use OpenApi\Attributes as OA;

class NewsBlockController extends Controller
{
    use ApiResponse;

    #[OA\Post(
        path: '/api/news/{newsId}/blocks',
        description: 'Додати контентний блок до новини',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['type'],
                    properties: [
                        new OA\Property(property: 'type', type: 'string', enum: ['text', 'image', 'text_image_right', 'text_image_left'], example: 'text'),
                        new OA\Property(property: 'order', type: 'integer', nullable: true, example: 1, description: 'Якщо не вказано — додається в кінець'),
                        new OA\Property(property: 'text_content', type: 'string', nullable: true, example: 'Текст блоку', description: 'Обов\'язково для text, text_image_right, text_image_left'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true, description: 'Обов\'язково для image, text_image_right, text_image_left'),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [
            new OA\PathParameter(name: 'newsId', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Блок створено', content: new OA\JsonContent(ref: '#/components/schemas/SuccessContentBlockResponse')),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')),
            new OA\Response(response: 404, description: 'Новину не знайдено', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreNewsBlockRequest $request, int $newsId)
    {
        try {
            $news = News::where('user_id', auth()->id())->findOrFail($newsId);

            $imageUrl = null;
            if ($request->hasFile('image')) {
                $imageUrl = $request->file('image')->store('content_blocks', 'public');
            }

            if ($request->filled('order')) {
                $news->contentBlocks()->where('order', '>=', $request->order)->increment('order');
                $order = $request->order;
            } else {
                $order = $news->contentBlocks()->max('order') + 1;
            }

            $block = $news->contentBlocks()->create([
                'type'         => $request->type,
                'text_content' => $request->text_content,
                'image_url'    => $imageUrl,
                'order'        => $order,
            ]);

            return $this->successResponse($block, 201);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при створенні блоку', 500, $e->getMessage());
        }
    }

    #[OA\Post(
        path: '/api/news/{newsId}/blocks/{blockId}',
        description: 'Оновити контентний блок новини',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    properties: [

                        new OA\Property(property: 'type', type: 'string', enum: ['text', 'image', 'text_image_right', 'text_image_left'], example: 'text'),
                        new OA\Property(property: 'order', type: 'integer', nullable: true, example: 2, description: 'Блоки поміняються місцями'),
                        new OA\Property(property: 'text_content', type: 'string', nullable: true, example: 'Оновлений текст'),
                        new OA\Property(property: 'image', type: 'string', format: 'binary', nullable: true),
                    ]
                )
            )
        ),
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [
            new OA\PathParameter(name: 'newsId', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\PathParameter(name: 'blockId', required: true, schema: new OA\Schema(type: 'integer'), example: 3),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Блок оновлено', content: new OA\JsonContent(ref: '#/components/schemas/SuccessContentBlockResponse')),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')),
            new OA\Response(response: 404, description: 'Новину або блок не знайдено', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateNewsBlockRequest $request, int $newsId, int $blockId)
    {
        try {
            $news  = News::where('user_id', auth()->id())->findOrFail($newsId);
            $block = $news->contentBlocks()->findOrFail($blockId);

            $data = [
                'type'         => $request->input('type', $block->type),
                'text_content' => $request->input('text_content', $block->text_content),
                'order'        => $request->input('order', $block->order),
            ];

            if ($request->hasFile('image')) {
                $data['image_url'] = $request->file('image')->store('content_blocks', 'public');
            }

            if ($request->filled('order') && (int) $request->order !== (int) $block->order) {
                $news->contentBlocks()
                    ->where('order', $request->order)
                    ->where('id', '!=', $block->id)
                    ->update(['order' => $block->order]);
            }

            $block->update($data);

            return $this->successResponse($block->fresh());

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину або блок не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при оновленні блоку', 500, $e->getMessage());
        }
    }

    #[OA\Delete(
        path: '/api/news/{newsId}/blocks/{blockId}',
        description: 'Видалити контентний блок новини',
        security: [['sanctum' => []]],
        tags: [OpenApiSpec::TAG_NEWS],
        parameters: [
            new OA\PathParameter(name: 'newsId', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\PathParameter(name: 'blockId', required: true, schema: new OA\Schema(type: 'integer'), example: 3),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Блок видалено', content: new OA\JsonContent(ref: '#/components/schemas/DeletedResponse')),
            new OA\Response(response: 401, description: 'Unauthorized', content: new OA\JsonContent(ref: '#/components/schemas/UnauthenticatedResponse')),
            new OA\Response(response: 404, description: 'Новину або блок не знайдено', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function destroy(int $newsId, int $blockId)
    {
        try {
            $news  = News::where('user_id', auth()->id())->findOrFail($newsId);
            $block = $news->contentBlocks()->findOrFail($blockId);

            $deletedOrder = $block->order;
            $block->delete();

            $news->contentBlocks()->where('order', '>', $deletedOrder)->decrement('order');

            return $this->successResponse(['message' => 'Блок видалено']);

        } catch (ModelNotFoundException $e) {
            return $this->notFoundResponse('Новину або блок не знайдено');
        } catch (\Exception $e) {
            return $this->errorResponse('Помилка при видаленні блоку', 500, $e->getMessage());
        }
    }
}
