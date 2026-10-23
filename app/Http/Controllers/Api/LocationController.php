<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class LocationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (!$request->user()->tokenCan('locations:write')) {
            return Response::failed('This session can only view locations.', null, 403);
        }
        $data = $request->validate([
            'client_fix_id' => ['nullable', 'uuid'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'min:0'],
            'full_address' => ['nullable', 'string', 'max:2000'],
            'place' => ['nullable', 'string', 'max:255'],
            'street' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:255'],
            'device_name' => ['nullable', 'string', 'max:255'],
            'captured_at' => ['nullable', 'date'],
        ]);

        $data['captured_at'] = isset($data['captured_at'])
            ? CarbonImmutable::parse($data['captured_at'])->utc()->toDateTimeString()
            : now('UTC')->toDateTimeString();
        return DB::transaction(function () use ($request, $data): JsonResponse {
            if (isset($data['client_fix_id'])) {
                $existing = $request->user()->locations()
                    ->where('client_fix_id', $data['client_fix_id'])->first();
                if ($existing) return Response::success('Location already saved.',
                    ['location' => $existing], 201);
            }

            // Offline fixes can arrive after newer online fixes. Place each one in time order.
            $before = $request->user()->locations()
                ->where('captured_at', '<=', $data['captured_at'])
                ->orderByDesc('captured_at')->orderByDesc('id')->first();
            if ($before && $this->distanceMeters(
                (float) $before->latitude, (float) $before->longitude,
                (float) $data['latitude'], (float) $data['longitude']) < 25) {
                return Response::success('Location unchanged.',
                    ['location' => $before, 'unchanged' => true]);
            }
            $after = $request->user()->locations()
                ->where('captured_at', '>', $data['captured_at'])
                ->orderBy('captured_at')->orderBy('id')->first();
            $location = isset($data['client_fix_id'])
                ? $request->user()->locations()->firstOrCreate(
                    ['client_fix_id' => $data['client_fix_id']], $data)
                : $request->user()->locations()->create($data);
            $capturedAt = strtotime($data['captured_at']);
            if ($before) $before->update([
                'stay_seconds' => max(0, $capturedAt - $before->captured_at->getTimestamp()),
            ]);
            if ($after) $location->update([
                'stay_seconds' => max(0, $after->captured_at->getTimestamp() - $capturedAt),
            ]);

            return Response::success('Location saved.', ['location' => $location], 201);
        });
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latitude = deg2rad($lat2 - $lat1);
        $longitude = deg2rad($lon2 - $lon1);
        $a = sin($latitude / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($longitude / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    public function latest(Request $request): JsonResponse
    {
        return Response::success('Latest location loaded.', [
            'location' => $request->user()->locations()
                ->orderByDesc('captured_at')->orderByDesc('id')->first(),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        return Response::success('Location history loaded.', $request->user()->locations()
            ->orderByDesc('captured_at')->paginate(50));
    }
}
