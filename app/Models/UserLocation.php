<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLocation extends Model
{
    protected $fillable = [
        'client_fix_id', 'latitude', 'longitude', 'accuracy_meters', 'full_address', 'place',
        'street', 'city', 'district', 'state', 'postal_code', 'country',
        'device_name', 'captured_at', 'stay_seconds',
    ];

    protected function casts(): array
    {
        return ['captured_at' => 'datetime', 'stay_seconds' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
