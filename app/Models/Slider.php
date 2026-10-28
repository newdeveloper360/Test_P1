<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = ['image_path', 'image_data', 'image_mime', 'title', 'sort_order', 'is_active'];

    protected $hidden = ['image_path', 'image_data', 'image_mime'];

    protected $casts = ['is_active' => 'boolean'];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return '/api/sliders/'.$this->id.'/image';
    }
}
