<?php

namespace App\Support;

class HttpImage
{
    public static function src(?string $url): ?string
    {
        if (! is_string($url) || $url === '') {
            return null;
        }

        if (! preg_match('/^https?:\/\/.+/i', $url)) {
            return null;
        }

        return $url;
    }

    public static function rule(): array
    {
        return ['nullable', 'string', 'max:2048', 'regex:/^https?:\/\/.+/i'];
    }
}
