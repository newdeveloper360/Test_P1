<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

final class StoredImage
{
    public static function response(?string $encoded, ?string $mime, ?string $legacyPath)
    {
        if ($encoded) {
            $bytes = base64_decode($encoded, true);
            abort_unless($bytes !== false && in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

            return response($bytes)->header('Content-Type', $mime)->header('X-Content-Type-Options', 'nosniff')
                ->header('Cache-Control', 'public, max-age=3600')->setEtag(hash('sha256', $bytes));
        }
        abort_unless($legacyPath && Storage::disk('public')->exists($legacyPath), 404);

        return Storage::disk('public')->response($legacyPath, null, ['Cache-Control' => 'public, max-age=3600', 'X-Content-Type-Options' => 'nosniff']);
    }
}
