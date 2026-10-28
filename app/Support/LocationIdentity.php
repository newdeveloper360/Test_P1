<?php

namespace App\Support;

final class LocationIdentity
{
    public static function normalized($value): string
    {
        return trim(preg_replace('/[^\pL\pN]+/u', ' ', mb_strtolower(trim((string) $value))) ?? '');
    }

    public static function detailed(array $data): bool
    {
        $broad = array_filter(array_map(fn ($k) => self::normalized($data[$k] ?? null), ['city', 'district', 'state', 'postal_code', 'country']));
        foreach (['street', 'place', 'full_address'] as $key) {
            $v = self::normalized($data[$key] ?? null);
            if ($v !== '' && ! in_array($v, $broad, true) && $v !== self::normalized(implode(' ', array_values($broad)))) {
                return true;
            }
        }

return false;
    }

    public static function same(array $old, array $new): bool
    {
        $a = self::normalized($old['full_address'] ?? null);
        $b = self::normalized($new['full_address'] ?? null);
        if ($a !== '' && $b !== '') {
            return $a === $b && ((self::detailed($old) && self::detailed($new)) || self::distance($old, $new) < 25);
        }
        $granular = false;
        $compared = false;
        foreach (['street', 'place', 'city', 'district', 'state', 'postal_code', 'country'] as $key) {
            $a = self::normalized($old[$key] ?? null);
            $b = self::normalized($new[$key] ?? null);
            if ($a === '' || $b === '') {
                continue;
            }$compared = true;
            if ($a !== $b) {
                return false;
            }
            if (in_array($key, ['street', 'place'], true) && self::detailed([$key => $a] + $old) && self::detailed([$key => $b] + $new)) {
                $granular = true;
            }
        }
        if ($granular) {
            return true;
        }

        return self::distance($old, $new) < 25 && (! $compared || ! self::detailed($old) || ! self::detailed($new));
    }

    private static function distance(array $a, array $b): float
    {
        foreach (['latitude', 'longitude'] as $k) {
            if (! isset($a[$k],$b[$k])) {
                return INF;
            }
        }
        $lat = deg2rad($b['latitude'] - $a['latitude']);
        $lon = deg2rad($b['longitude'] - $a['longitude']);
        $v = sin($lat / 2) ** 2 + cos(deg2rad($a['latitude'])) * cos(deg2rad($b['latitude'])) * sin($lon / 2) ** 2;

        return 6371000 * 2 * asin(sqrt(min(1, max(0, $v))));
    }
}
