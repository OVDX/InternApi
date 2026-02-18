<?php

namespace App\Services;

use App\Models\News;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class NewsService
{

    public function processContentBlocks(News $news, array $blocks, $request, array &$uploadedFiles = []): void
    {
        $blocksWithOriginalIndex = [];
        foreach ($blocks as $originalIndex => $blockData) {
            $blocksWithOriginalIndex[] = [
                'data' => $blockData,
                'original_index' => $originalIndex,
            ];
        }

        usort($blocksWithOriginalIndex, fn($a, $b) =>
            ($a['data']['order'] ?? 0) <=> ($b['data']['order'] ?? 0)
        );

        $existingBlockIds = [];

        foreach ($blocksWithOriginalIndex as $newIndex => $item) {
            $blockData = $item['data'];
            $originalIndex = $item['original_index'];
            $correctOrder = $newIndex + 1;

            if (isset($blockData['id'])) {
                $block = $news->contentBlocks()->find($blockData['id']);
                if ($block) {
                    $this->updateBlock($block, $blockData, $originalIndex, $correctOrder, $request, $uploadedFiles);
                    $existingBlockIds[] = $block->id;
                }
            } else {
                $newBlock = $this->createBlock($news, $blockData, $originalIndex, $correctOrder, $request, $uploadedFiles);
                $existingBlockIds[] = $newBlock->id;
            }
        }

        $this->deleteUnusedBlocks($news, $existingBlockIds);
    }


    private function updateBlock($block, array $blockData, int $originalIndex, int $order, $request, array &$uploadedFiles): void
    {
        $type = $blockData['type'];

        $updateData = [
            'type' => $type,
            'order' => $order,
        ];

        if ($type === 'text') {
            $updateData['text_content'] = $blockData['text_content'] ?? null;
            $this->deleteImage($block->image_url);
            $updateData['image_url'] = null;

        } elseif ($type === 'image') {
            $updateData['text_content'] = null;
            $updateData['image_url'] = $this->handleImageUpload(
                $request,
                "content_blocks.{$originalIndex}.image",
                $block->image_url,
                $uploadedFiles
            );

        } elseif (in_array($type, ['text_image_right', 'text_image_left'])) {
            $updateData['text_content'] = $blockData['text_content'] ?? null;
            $updateData['image_url'] = $this->handleImageUpload(
                $request,
                "content_blocks.{$originalIndex}.image",
                $block->image_url,
                $uploadedFiles
            );
        }

        $block->update($updateData);
    }


    private function createBlock(News $news, array $blockData, int $originalIndex, int $order, $request, array &$uploadedFiles)
    {
        $type = $blockData['type'];
        $imageUrl = null;

        if ($request->hasFile("content_blocks.{$originalIndex}.image")) {
            $imageUrl = $request->file("content_blocks.{$originalIndex}.image")
                ->store('content_blocks', 'public');
            $uploadedFiles[] = $imageUrl;
        }

        return $news->contentBlocks()->create([
            'type' => $type,
            'text_content' => in_array($type, ['text', 'text_image_right', 'text_image_left'])
                ? ($blockData['text_content'] ?? null)
                : null,
            'image_url' => $imageUrl,
            'order' => $order,
        ]);
    }


    private function handleImageUpload($request, string $fieldName, ?string $oldImage, array &$uploadedFiles): ?string
    {
        if ($request->hasFile($fieldName)) {
            $newImage = $request->file($fieldName)->store('content_blocks', 'public');
            $uploadedFiles[] = $newImage;
            $this->deleteImage($oldImage);
            return $newImage;
        }

        return $oldImage;
    }


    public function deleteImage(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }


    private function deleteUnusedBlocks(News $news, array $keepIds): void
    {
        $blocksToDelete = $news->contentBlocks()->whereNotIn('id', $keepIds)->get();

        foreach ($blocksToDelete as $block) {
            $this->deleteImage($block->image_url);
            $block->delete();
        }
    }


    public function cleanupFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->deleteImage($file);
        }
    }
}
