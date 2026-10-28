<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $t) {
            foreach (['logo', 'banner_image'] as $prefix) {
                $t->longText($prefix.'_data')->nullable();
                $t->string($prefix.'_mime', 40)->nullable();
            }
        });
        Schema::table('sliders', function (Blueprint $t) {
            $t->longText('image_data')->nullable();
            $t->string('image_mime', 40)->nullable();
        });
        foreach (['settings' => ['logo', 'banner_image'], 'sliders' => ['image']] as $table => $prefixes) {
            DB::table($table)->orderBy('id')->chunkById(100, function ($rows) use ($table, $prefixes) {
                foreach ($rows as $row) {
                    foreach ($prefixes as $prefix) {
                        $path = $row->{$prefix.'_path'} ?? null;
                        if ($path && Storage::disk('public')->exists($path)) {
                            DB::table($table)->where('id', $row->id)->update([
                                $prefix.'_data' => base64_encode(Storage::disk('public')->get($path)),
                                $prefix.'_mime' => Storage::disk('public')->mimeType($path)]);
                        }
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('settings', fn (Blueprint $t) => $t->dropColumn(['logo_data', 'logo_mime', 'banner_image_data', 'banner_image_mime']));
        Schema::table('sliders', fn (Blueprint $t) => $t->dropColumn(['image_data', 'image_mime']));
    }
};
