<?php

namespace App\Support;

use App\Models\LocationFix;
use App\Models\User;
use App\Models\UserLocation;

/** Visits are a projection; immutable capture evidence is independent of history deduplication. */
final class LocationTimeline
{
    public const FIELDS = ['latitude', 'longitude', 'accuracy_meters', 'full_address', 'place', 'street', 'city', 'district', 'state', 'postal_code', 'country', 'device_name', 'captured_at'];

    public function insert(User $user, LocationFix $fix): ?UserLocation
    {
        if (! $fix->record_visit) {
            return null;
        }
        $previous = LocationFix::where('user_id', $user->id)->where('record_visit', true)->where('id', '!=', $fix->id)->orderByDesc('captured_at')->orderByDesc('id')->first();
        if ($previous && $fix->captured_at->lt($previous->captured_at)) {
            $this->rebuild($user);

            return UserLocation::find($fix->fresh()->visit_id);
        }
        $visit = $previous?->visit_id ? UserLocation::find($previous->visit_id) : null;
        if ($visit && LocationIdentity::same($previous->toArray(), $fix->toArray())) {
            $gap = $fix->captured_at->getTimestamp() - $previous->captured_at->getTimestamp();
            $visit->update(['last_seen_at' => $fix->captured_at, 'coverage_status' => $visit->coverage_status === 'unknown' || $gap > 120 ? 'unknown' : 'observed']);
        } else {
            $visit = $user->locations()->create($fix->only(self::FIELDS) + ['client_fix_id' => $fix->client_fix_id, 'last_seen_at' => $fix->captured_at, 'coverage_status' => $fix->quality_status === 'precise' ? 'observed' : 'unknown']);
            if ($previous?->visit_id) {
                $old = UserLocation::findOrFail($previous->visit_id);
                $old->update([
                    'stay_seconds' => max(0, $fix->captured_at->getTimestamp() - $old->captured_at->getTimestamp()),
                    'coverage_status' => $fix->captured_at->getTimestamp() - $previous->captured_at->getTimestamp() > 120 ? 'unknown' : $old->coverage_status]);
            }
        }
        $fix->update(['visit_id' => $visit->id]);

        return $visit->refresh();
    }

    private function rebuild(User $user): void
    {
        // Normal appends are O(1). Delayed evidence requires a chronological projection replay.
        $events = LocationFix::where('user_id', $user->id)->where('record_visit', true)->orderBy('captured_at')->orderBy('id')->get();
        $groups = [];
        foreach ($events as $event) {
            $last = count($groups) - 1;
            if ($last < 0 || ! LocationIdentity::same($groups[$last][count($groups[$last]) - 1]->toArray(), $event->toArray())) {
                $groups[] = [$event];
            } else {
                $groups[$last][] = $event;
            }
        }
        $used = [];
        foreach ($groups as $index => $group) {
            $first = $group[0];
            $last = $group[count($group) - 1];
            $visit = $first->visit_id && ! in_array($first->visit_id, $used, true) ? $user->locations()->find($first->visit_id) : null;
            $coverage = $first->quality_status === 'precise' ? 'observed' : 'unknown';
            foreach ($group as $i => $event) {
                if ($i > 0 && $event->captured_at->getTimestamp() - $group[$i - 1]->captured_at->getTimestamp() > 120) {
                    $coverage = 'unknown';
                }
            }
            $next = $groups[$index + 1][0] ?? null;
            if ($next && $next->captured_at->getTimestamp() - $last->captured_at->getTimestamp() > 120) {
                $coverage = 'unknown';
            }
            $data = $first->only(self::FIELDS) + ['client_fix_id' => $first->client_fix_id, 'last_seen_at' => $last->captured_at, 'coverage_status' => $coverage,
                'stay_seconds' => $next ? max(0, $next->captured_at->getTimestamp() - $first->captured_at->getTimestamp()) : null];
            if ($visit) {
                $visit->update($data);
            } else {
                $visit = $user->locations()->create($data);
            }
            $used[] = $visit->id;
            LocationFix::whereIn('id', array_map(fn ($e) => $e->id, $group))->update(['visit_id' => $visit->id]);
        }
        // Only consolidated projection rows are removed. Capture evidence is retained.
        $user->locations()->whereNotIn('id',$used)->delete();
    }
}
