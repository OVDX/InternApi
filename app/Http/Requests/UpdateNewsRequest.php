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
            'title' => 'sometimes|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,jpg,png|max:2048',
            'short_description' => 'sometimes|string',

            'category_ids' => 'nullable|array|max:10',
            'category_ids.*' => 'integer|exists:categories,id',

            'content_blocks' => 'sometimes|array|max:50',
            'content_blocks.*.id' => 'sometimes|integer|exists:content_blocks,id',
            'content_blocks.*.type' => 'required|in:text,image,text_image_right,text_image_left',
            'content_blocks.*.order' => 'required|integer|min:1|distinct',
            'content_blocks.*.text_content' => 'nullable|string',
            'content_blocks.*.image' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'Назва не може перевищувати 255 символів',
            'image.image' => 'Файл повинен бути зображенням',
            'image.mimes' => 'Підтримуються тільки форматі: jpeg, jpg, png',
            'image.max' => 'Розмір зображення не може перевищувати 2MB',

            'category_ids.array' => 'Категорії мають бути масивом',
            'category_ids.max' => 'Максимум 10 категорій',
            'category_ids.*.integer' => 'ID категорії має бути числом',
            'category_ids.*.exists' => 'Категорія не існує',

            'content_blocks.max' => 'Максимальна кількість блоків - 50',
            'content_blocks.*.id.exists' => 'Блок з таким ID не існує',
            'content_blocks.*.type.required' => 'Тип блоку є обов\'язковим',
            'content_blocks.*.type.in' => 'Тип має бути: text, image, text_image_right, text_image_left',
            'content_blocks.*.order.required' => 'Порядковий номер є обов\'язковим',
            'content_blocks.*.order.integer' => 'Порядковий номер має бути цілим числом',
            'content_blocks.*.order.min' => 'Порядковий номер має бути більше 0',
            'content_blocks.*.order.distinct' => 'Порядкові номери блоків мають бути унікальними',
            'content_blocks.*.image.image' => 'Файл повинен бути зображенням',
            'content_blocks.*.image.mimes' => 'Підтримуються тільки форматі: jpeg, jpg, png',
            'content_blocks.*.image.max' => 'Розмір зображення не може перевищувати 2MB',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $categoryIds = $this->input('category_ids', []);

            if (!empty($categoryIds)) {
                $activeCount = Category::whereIn('id', $categoryIds)
                    ->where('is_active', true)
                    ->count();

                if ($activeCount !== count($categoryIds)) {
                    $validator->errors()->add('category_ids',
                        'Одна або декілька категорій неактивні або не існують');
                }
            }

            $blocks = $this->input('content_blocks', []);
            $newsId = $this->route('id');

            $blockIds = array_filter(array_column($blocks, 'id'));

            if (!empty($blockIds)) {
                if (count($blockIds) !== count(array_unique($blockIds))) {
                    $validator->errors()->add('content_blocks',
                        'ID блоків мають бути унікальними');
                    return;
                }

                $validCount = ContentBlock::where('news_id', $newsId)
                    ->whereIn('id', $blockIds)
                    ->count();

                if ($validCount !== count($blockIds)) {
                    $validator->errors()->add('content_blocks',
                        'Один або декілька блоків не належать цій новині');
                    return;
                }
            }

            foreach ($blocks as $index => $block) {
                $type = $block['type'] ?? null;
                $blockId = $block['id'] ?? null;

                if ($type === 'text' && empty(trim($block['text_content'] ?? ''))) {
                    $validator->errors()->add(
                        "content_blocks.{$index}.text_content",
                        'Текст є обов\'язковим для типу text'
                    );
                }

                if ($type === 'image') {
                    if (!$blockId && !$this->hasFile("content_blocks.{$index}.image")) {
                        $validator->errors()->add(
                            "content_blocks.{$index}.image",
                            'Зображення є обов\'язковим для нового блоку типу image'
                        );
                    }

                    if ($blockId) {
                        $existingBlock = ContentBlock::find($blockId);
                        if ($existingBlock && $existingBlock->type !== 'image'
                            && !$this->hasFile("content_blocks.{$index}.image")) {
                            $validator->errors()->add(
                                "content_blocks.{$index}.image",
                                'При зміні типу на image необхідно завантажити зображення'
                            );
                        }
                    }
                }

                if (in_array($type, ['text_image_right', 'text_image_left'])) {
                    if (empty(trim($block['text_content'] ?? ''))) {
                        $validator->errors()->add(
                            "content_blocks.{$index}.text_content",
                            'Текст є обов\'язковим для цього типу блоку'
                        );
                    }

                    if (!$blockId && !$this->hasFile("content_blocks.{$index}.image")) {
                        $validator->errors()->add(
                            "content_blocks.{$index}.image",
                            'Зображення є обов\'язковим для нового блоку цього типу'
                        );
                    }

                    if ($blockId) {
                        $existingBlock = ContentBlock::find($blockId);
                        if ($existingBlock && !in_array($existingBlock->type, ['text_image_right', 'text_image_left', 'image'])
                            && !$this->hasFile("content_blocks.{$index}.image")) {
                            $validator->errors()->add(
                                "content_blocks.{$index}.image",
                                'При зміні типу на text_image необхідно завантажити зображення'
                            );
                        }
                    }
                }
            }
        });
    }
}
