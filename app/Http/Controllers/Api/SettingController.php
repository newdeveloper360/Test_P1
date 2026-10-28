<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\StoredImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class SettingController extends Controller
{
    public function show(): JsonResponse
    {
        return Response::success('App settings loaded.', Setting::current());
    }

    public function image(string $type)
    {
        if (! in_array($type, ['logo', 'banner'], true)) {
            abort(404);
        }
        $setting = Setting::current();
        $path = $type === 'logo' ? $setting->logo_path : $setting->banner_image_path;
        $prefix = $type === 'logo' ? 'logo' : 'banner_image';

        return StoredImage::response($setting->{$prefix.'_data'}, $setting->{$prefix.'_mime'}, $path);
    }
}
