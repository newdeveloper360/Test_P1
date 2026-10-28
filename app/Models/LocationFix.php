<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationFix extends Model
{
    protected $guarded = ['id'];

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected function casts(): array
    {
        return ['captured_at' => 'datetime', 'received_at' => 'datetime', 'record_visit' => 'boolean', 'visit_id' => 'integer'];
    }
}
