<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['app_name', 'banner_text', 'banner_image_url', 'logo_url',
        'maintenance_mode', 'maintenance_message', 'android_version_code', 'android_update_url'];

    protected $casts = ['maintenance_mode' => 'boolean', 'android_version_code' => 'integer'];
    protected $hidden = ['logo_path', 'banner_image_path'];

    public static function current(): self
    {
        return static::query()->findOrFail(1);
    }
}
