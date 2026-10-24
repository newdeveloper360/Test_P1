<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $fillable = ['title', 'slug', 'short_description', 'featured_image', 'content',
        'featured_image_data', 'featured_image_mime', 'meta_title', 'meta_description', 'meta_keywords', 'is_published'];
    protected $hidden = ['featured_image_data'];
    protected $casts = ['is_published' => 'boolean'];
}
