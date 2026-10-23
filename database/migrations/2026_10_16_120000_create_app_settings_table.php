<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name', 60)->default('IP Tracker');
            $table->string('banner_text', 200)->nullable();
            $table->string('banner_image_url', 2048)->nullable();
            $table->string('logo_url', 2048)->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->string('maintenance_message', 200)->nullable();
            $table->timestamps();
        });
        DB::table('settings')->insert([
            'id' => 1, 'app_name' => 'IP Tracker', 'maintenance_mode' => false,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('app_settings');
    }
};
