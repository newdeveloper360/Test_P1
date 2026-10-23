<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['image_path', 'title', 'sort_order', 'is_active'];
    protected $hidden = ['image_path'];
    protected $casts = ['is_active' => 'boolean'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return '/api/sliders/'.$this->id.'/image';
    }
}
