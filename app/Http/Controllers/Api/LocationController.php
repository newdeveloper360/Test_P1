<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationFix;
use App\Support\LocationHistoryFilters;
use App\Support\LocationIdentity;
use App\Support\LocationTimeline;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class LocationController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (! $request->user()->tokenCan('locations:write')) {
            return Response::failed('This session can only view locations.', null, 403);
        }
        $rules = ['client_fix_id' => ['nullable', 'uuid'], 'latitude' => ['required', 'numeric', 'between:-90,90'], 'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => ['nullable', 'numeric', 'gt:0', 'max:10000'], 'captured_at' => ['nullable', 'date', 'before_or_equal:'.now('UTC')->addMinutes(2)->toIso8601String()],
            'record_visit' => ['sometimes', 'boolean']];
        foreach (['full_address' => 2000, 'place' => 255, 'street' => 255, 'city' => 255, 'district' => 255, 'state' => 255, 'postal_code' => 20, 'country' => 255, 'device_name' => 255] as $key => $max) {
            $rules[$key] = ['nullable', 'string', 'max:'.$max];
        }
        $data = $request->validate($rules);
        $data['captured_at'] = isset($data['captured_at']) ? CarbonImmutable::parse($data['captured_at'])->utc() : now('UTC');
        $accuracy = $data['accuracy_meters'] ?? null;
        $data['quality_status'] = $accuracy === null ? 'unknown' : ($accuracy <= 35 ? 'precise' : 'approximate');
        $data['address_status'] = LocationIdentity::detailed($data) ? 'resolved' : 'pending';
        // Preserve legacy clients; explicit modern visit requests require precise, detailed evidence.
        $data['record_visit'] = $request->has('record_visit') ? ($request->boolean('record_visit') && $data['quality_status'] === 'precise' && $data['address_status'] === 'resolved') : ($accuracy === null || $accuracy <= 35);
        $data['client_fix_id'] = $data['client_fix_id'] ?? (string) Str::uuid();
        $data['received_at'] = now('UTC');

        return DB::transaction(function () use ($request, $data) {
            $user = $request->user()->newQuery()->whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            if ($user->locations_cleared_at && $data['captured_at']->lessThanOrEqualTo($user->locations_cleared_at)) {
                return Response::success('This older record was cleared by the administrator.', ['discarded' => true]);
            }
            $existing = LocationFix::where('user_id', $user->id)->where('client_fix_id', $data['client_fix_id'])->first();
            if ($existing) {
                if (abs((float) $existing->latitude - (float) $data['latitude']) > 0.0000001
                    || abs((float) $existing->longitude - (float) $data['longitude']) > 0.0000001
                    || ($request->has('captured_at') && ! $existing->captured_at->equalTo($data['captured_at']))) {
                    return Response::failed('This capture ID belongs to a different location or time.', null, 409);
                }
                if (! $existing->record_visit && $data['record_visit']) {
                    $enriched = $data;
                    foreach (['latitude', 'longitude', 'captured_at', 'accuracy_meters', 'quality_status', 'received_at'] as $key) {
                        $enriched[$key] = $existing->$key;
                    }
                    $enriched['record_visit'] = $existing->quality_status === 'precise' && LocationIdentity::detailed($enriched);
                    $existing->update($enriched);
                    (new LocationTimeline)->insert($user, $existing);
                }

                return Response::success('Location already saved.', ['location' => $existing->fresh(), 'visit' => $user->locations()->find($existing->visit_id)], 201);
            }
            $fix = LocationFix::create(['user_id' => $user->id] + $data);
            $before = $fix->record_visit ? $user->locations()->count() : 0;
            $visit = (new LocationTimeline)->insert($user, $fix);
            $unchanged = $visit && $user->locations()->count() === $before;

            return Response::success($unchanged ? 'Location unchanged.' : 'Location saved.', ['location' => $fix->fresh(), 'visit' => $visit, 'unchanged' => (bool) $unchanged], $unchanged ? 200 : 201);
        });
    }

    public function latest(Request $request): JsonResponse
    {
        $user = $request->user();

        return Response::success('Latest location loaded.', [
            'location' => LocationFix::where('user_id', $user->id)->orderByDesc('captured_at')->orderByDesc('id')->first(),
            'visit' => $user->locations()->orderByDesc('captured_at')->orderByDesc('id')->first()]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = LocationHistoryFilters::apply($request->user()->locations(), $request);

        return Response::success('Location history loaded.', $query->orderByDesc('captured_at')->orderByDesc('id')->paginate(50)->withQueryString());
    }
}
