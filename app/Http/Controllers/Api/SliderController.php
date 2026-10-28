<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Support\StoredImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;

class SliderController extends Controller
{
    public function index(): JsonResponse
    {
        return Response::success('Sliders loaded.', Slider::query()
            ->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get());
    }

    public function image(Slider $slider)
    {
        abort_unless($slider->is_active, 404);

        return StoredImage::response($slider->image_data, $slider->image_mime, $slider->image_path);
    }
}
