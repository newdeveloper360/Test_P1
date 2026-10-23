<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SliderController extends Controller
{
    public function index(): JsonResponse
    {
        return Response::success('Sliders loaded.', Slider::query()
            ->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get());
    }

    public function image(Slider $slider)
    {
        if (!$slider->is_active || !Storage::disk('public')->exists($slider->image_path)) abort(404);
        return Storage::disk('public')->response($slider->image_path, null,
            ['Cache-Control' => 'public, max-age=86400']);
    }
}
