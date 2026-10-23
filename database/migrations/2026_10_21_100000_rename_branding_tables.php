<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('app_settings') && !Schema::hasTable('settings')) {
            Schema::rename('app_settings', 'settings');
        }
        if (Schema::hasTable('app_sliders') && !Schema::hasTable('sliders')) {
            Schema::rename('app_sliders', 'sliders');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settings') && !Schema::hasTable('app_settings')) {
            Schema::rename('settings', 'app_settings');
        }
        if (Schema::hasTable('sliders') && !Schema::hasTable('app_sliders')) {
            Schema::rename('sliders', 'app_sliders');
        }
    }
};
