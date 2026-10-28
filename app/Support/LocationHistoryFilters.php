<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

final class LocationHistoryFilters
{
    public static function apply($query, Request $request)
    {
        $format = '/^\d{4}-\d{2}-\d{2}(?:T\d{2}:\d{2}(?::\d{2}(?:\.\d{1,6})?)?(?:Z|[+-]\d{2}:\d{2})?)?$/';
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date', 'regex:'.$format], 'to' => ['nullable', 'date', 'regex:'.$format],
            'timezone' => ['nullable', 'timezone']]);
        $zone = $data['timezone'] ?? config('app.timezone', 'UTC');
        $parse = function ($value, $end) use ($zone) {
            if (! $value) {
                return null;
            }
            $date = CarbonImmutable::parse($value, $zone);
            if (strlen($value) === 10) {
                $date = $end ? $date->endOfDay() : $date->startOfDay();
            } elseif ($end && strlen($value) === 16) {
                $date = $date->endOfMinute();
            }

            return $date->utc();
        };
        $from = $parse($data['from'] ?? null, false);
        $to = $parse($data['to'] ?? null, true);
        if ($from && $to && $to->lt($from)) {
            throw ValidationException::withMessages(['to' => 'To date/time must be after or equal to From date/time.']);
        }
        if ($from) {
            $query->where('captured_at', '>=', $from->format('Y-m-d H:i:s'));
        }
        if ($to) {
            $query->where('captured_at', '<=', $to->format('Y-m-d H:i:s'));
        }
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->whereLike('full_address', '%'.$data['q'].'%')->orWhereLike('city', '%'.$data['q'].'%')->orWhereLike('street', '%'.$data['q'].'%'));
        }

        return $query;
    }
}
