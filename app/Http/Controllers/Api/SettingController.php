<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function show(): JsonResponse
    {
        return Response::success('App settings loaded.', Setting::current());
    }

    public function image(string $type)
    {
        if (!in_array($type, ['logo', 'banner'], true)) abort(404);
        $setting = Setting::current();
        $path = $type === 'logo' ? $setting->logo_path : $setting->banner_image_path;
        if (!$path || !Storage::disk('public')->exists($path)) abort(404);
        return Storage::disk('public')->response($path, null,
            ['Cache-Control' => 'public, max-age=86400']);
    }
}
