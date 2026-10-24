<?php

namespace App\Support;

final class WebsiteSettings
{
    public static function rules(): array
    {
        $rules = [
            'site_name' => ['nullable', 'string', 'max:100'],
            'site_url' => ['nullable', 'url:http,https', 'max:2048'],
            'seo_title' => ['nullable', 'string', 'max:180'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'seo_keywords' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9 ()-]{7,20}$/'],
            'whatsapp' => ['nullable', 'string', 'regex:/^\+?[0-9]{10,15}$/'],
            'support_email' => ['nullable', 'email:rfc', 'max:254'],
        ];
        foreach (['facebook_url', 'instagram_url', 'x_url', 'linkedin_url', 'youtube_url'] as $field) {
            $rules[$field] = ['nullable', 'url:http,https', 'max:2048'];
        }
        return $rules;
    }
}
