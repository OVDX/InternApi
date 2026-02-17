<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AvatarService
{
    private const STORAGE_DISK = 'public';
    private const STORAGE_PATH = 'avatars';

    public function upload(UploadedFile $file, User $user): array
    {
        $this->deleteOldAvatar($user);

        $path = $file->store(self::STORAGE_PATH, self::STORAGE_DISK);

        $user->update(['avatar' => $path]);

        return [
            'message' => 'Аватар успішно завантажено',
            'avatar' => $path,
            'avatar_url' => asset('storage/' . $path)
        ];
    }

    public function delete(User $user): void
    {
        $this->deleteOldAvatar($user);
        $user->update(['avatar' => null]);
    }

    private function deleteOldAvatar(User $user): void
    {
        if ($user->avatar && Storage::disk(self::STORAGE_DISK)->exists($user->avatar)) {
            Storage::disk(self::STORAGE_DISK)->delete($user->avatar);
        }
    }
}
