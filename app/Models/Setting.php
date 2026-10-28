<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['app_name', 'banner_text', 'banner_image_url', 'logo_url',
        'maintenance_mode', 'maintenance_message', 'android_version_code', 'android_update_url',
        'site_name', 'site_url', 'seo_title', 'seo_description', 'seo_keywords', 'phone', 'whatsapp',
        'support_email', 'facebook_url', 'instagram_url', 'x_url', 'linkedin_url', 'youtube_url'];

    protected $casts = ['maintenance_mode' => 'boolean', 'android_version_code' => 'integer'];

    protected $hidden = ['logo_path', 'banner_image_path', 'logo_data', 'logo_mime', 'banner_image_data', 'banner_image_mime'];

    public static function current(): self
    {
        return static::query()->findOrFail(1);
    }
}
