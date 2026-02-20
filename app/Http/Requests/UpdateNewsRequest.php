<?php

namespace App\Http\Requests;

use App\Models\Category;
use App\Models\ContentBlock;
use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'image' => 'nullable|sometimes|image|mimes:jpeg,jpg,png|max:2048',
            'short_description' => 'sometimes|required|string|max:1000',
            'is_published' => 'sometimes|boolean',

            'category_ids' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;

                    $ids = is_string($value)
                        ? array_map('intval', explode(',', $value))
                        : (array) $value;

                    $validIds = array_filter($ids, fn($id) => $id > 0);

                    if (empty($validIds)) {
                        $fail('Виберіть хоча б одну категорію');
                        return;
                    }

                    if (count($validIds) > 10) {
                        $fail('Максимум 10 категорій');
                        return;
                    }

                    $invalidIds = array_diff($validIds, Category::pluck('id')->toArray());
                    if (!empty($invalidIds)) {
                        $fail('Категорії не існують: ' . implode(', ', $invalidIds));
                    }
                },
            ],

            'content_blocks' => 'sometimes|nullable|array|max:50',
            'content_blocks.*.id' => 'sometimes|nullable|integer|exists:content_blocks,id',
            'content_blocks.*.type' => 'required|in:text,image,text_image_right,text_image_left',
            'content_blocks.*.order' => 'required|integer|min:1|distinct',
            'content_blocks.*.text_content' => 'nullable|string|max:5000',
            'content_blocks.*.image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Назва є обов\'язковою при оновленні',
            'title.max' => 'Назва не більше 255 символів',
            'short_description.required' => 'Опис обов\'язковий',
            'image.image' => 'Файл має бути зображенням (jpeg,jpg,png)',
            'image.max' => 'Зображення до 2MB',

            'category_ids' => 'Невірний формат: "1,3" або [1,3]',

            'content_blocks.max' => 'Макс. 50 блоків',
            'content_blocks.*.id.exists' => 'Блок не існує',
            'content_blocks.*.type.required' => 'Тип блоку обов\'язковий',
            'content_blocks.*.order.distinct' => 'Унікальні порядки',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $newsId = $this->route('id');

            $categoryIds = $this->parseCategoryIds($this);
            if (!empty($categoryIds)) {
                $inactiveCount = Category::whereIn('id', $categoryIds)
                    ->where('is_active', false)->count();
                if ($inactiveCount > 0) {
                    $validator->errors()->add('category_ids', 'Є неактивні категорії');
                }
            }

            $blocks = $this->input('content_blocks', []);
            $blockIds = array_filter(array_column($blocks, 'id'));

            if (!empty($blockIds)) {
                if (count($blockIds) !== count(array_unique($blockIds))) {
                    $validator->errors()->add('content_blocks', 'Унікальні ID блоків');
                    return;
                }

                $foreignCount = ContentBlock::where('news_id', $newsId)
                    ->whereIn('id', $blockIds)->count();
                if ($foreignCount !== count($blockIds)) {
                    $validator->errors()->add('content_blocks', 'Блоки належать цій новині');
                }
            }

            foreach ($blocks as $index => $block) {
                $type = $block['type'] ?? null;
                $blockId = $block['id'] ?? null;

                match($type) {
                    'text' => $this->validateTextBlock($validator, $index, $block),
                    'image' => $this->validateImageBlock($validator, $index, $block, $blockId),
                    'text_image_right', 'text_image_left' =>
                    $this->validateTextImageBlock($validator, $index, $block, $blockId),
                    default => $validator->errors()->add("content_blocks.{$index}.type", 'Невідомий тип'),
                };
            }
        });
    }

    private function parseCategoryIds($request): array
    {
        if (!$request->filled('category_ids')) return [];
        $value = $request->category_ids;
        if (is_string($value)) {
            return array_filter(array_map('intval', explode(',', $value)), fn($id) => $id > 0);
        }
        return array_filter($value ?? [], fn($id) => is_numeric($id) && $id > 0);
    }

    private function validateTextBlock($validator, $index, $block)
    {
        if (empty(trim($block['text_content'] ?? ''))) {
            $validator->errors()->add("content_blocks.{$index}.text_content", 'Текст обов\'язковий');
        }
    }

    private function validateImageBlock($validator, $index, $block, $blockId)
    {
        if (!$blockId && !$this->hasFile("content_blocks.{$index}.image")) {
            $validator->errors()->add("content_blocks.{$index}.image", 'Зображення обов\'язкове');
        }
    }

    private function validateTextImageBlock($validator, $index, $block, $blockId)
    {
        if (empty(trim($block['text_content'] ?? ''))) {
            $validator->errors()->add("content_blocks.{$index}.text_content", 'Текст обов\'язковий');
        }
        if (!$blockId && !$this->hasFile("content_blocks.{$index}.image")) {
            $validator->errors()->add("content_blocks.{$index}.image", 'Зображення обов\'язкове');
        }
    }
}
