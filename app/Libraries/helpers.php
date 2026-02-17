<?php

if (!function_exists('getFileUrl')) {

    function getFileUrl(?string $path, ?string $default = null): ?string
    {
        if (!$path) {
            return $default;
        }

        return asset('storage/' . $path);
    }
}

if (!function_exists('getImageUrl')) {

    function getImageUrl(?string $path, ?string $default = null): ?string
    {
        return getFileUrl($path, $default);
    }
}

if (!function_exists('getAvatarUrl')) {

    function getAvatarUrl(?string $path): string
    {
        if (!$path) {
            return asset('images/default-avatar.png');
        }

        return asset('storage/' . $path);
    }
}
