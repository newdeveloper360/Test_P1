<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::query()->firstOrCreate(['id' => 1], [
            'app_name' => 'IP Tracker',
            'banner_text' => 'Your location and history in one place',
            'maintenance_mode' => false,
            'android_version_code' => 1,
            'android_update_url' => null,
        ]);
    }
}
