<?php


namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class NewsBlockService
{
    /**
     * Отримати масив картинок для блоків з request
     */
    public function extractBlockImages(Request $request): array
    {
        $blockImages = [];

        foreach ($request->allFiles() as $key => $file) {
            if (preg_match('/^block_images\[(\d+)]$/', $key, $matches)) {
                $blockImages[(int)$matches[1]] = $file;
            }
        }

        if (empty($blockImages) && $request->hasFile('block_images')) {
            $files = $request->file('block_images');

            if (is_array($files)) {
                $blockImages = $files;
            } else if ($files instanceof UploadedFile) {
                $blockImages = [$files];
            }
        }

        return $blockImages;
    }

    /**
     * Створити контентні блоки для новини
     */
    public function createContentBlocks($news, array $blocks, array $blockImages): void
    {
        foreach ($blocks as $blockData) {
            $imageUrl = null;

            if (isset($blockData['has_image'])
                && $blockData['has_image']
                && isset($blockData['image_index'])
                && isset($blockImages[$blockData['image_index']])) {

                $imageUrl = $blockImages[$blockData['image_index']]->store('content_blocks', 'public');
            }

            $news->contentBlocks()->create([
                'type' => $blockData['type'],
                'text_content' => $blockData['text_content'] ?? null,
                'image_url' => $imageUrl,
                'order' => $blockData['order']
            ]);
        }
    }

    /**
     * Валідувати та декодувати JSON блоків
     */
    public function validateAndDecodeBlocks(?string $blocksJson): ?array
    {
        if (!$blocksJson) {
            return null;
        }

        $blocks = json_decode($blocksJson, true);

        if (!is_array($blocks)) {
            throw new \InvalidArgumentException('Невалідний JSON в полі blocks');
        }

        return $blocks;
    }
}
