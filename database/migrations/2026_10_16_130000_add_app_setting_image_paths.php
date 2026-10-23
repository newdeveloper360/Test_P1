<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('banner_image_path')->nullable();
            $table->string('logo_path')->nullable();
        });
    }

    public function down(): void
    {
        $tableName = Schema::hasTable('settings') ? 'settings' : 'app_settings';
        Schema::table($tableName, function (Blueprint $table) {
            $table->dropColumn(['banner_image_path', 'logo_path']);
        });
    }
};
