<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        foreach (['site_name', 'site_url', 'seo_title', 'seo_description', 'seo_keywords',
            'phone', 'whatsapp', 'support_email', 'facebook_url', 'instagram_url',
            'x_url', 'linkedin_url', 'youtube_url'] as $column) {
            // TEXT keeps the existing MySQL settings row below its inline row-size limit.
            $exists = Schema::hasColumn('settings', $column);
            Schema::table('settings', function (Blueprint $table) use ($column, $exists) {
                $field = $table->text($column)->nullable();
                if ($exists) $field->change();
            });
        }
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 180)->unique();
            $table->string('short_description', 500);
            $table->string('featured_image')->nullable();
            $table->longText('featured_image_data')->nullable();
            $table->string('featured_image_mime', 40)->nullable();
            $table->longText('content');
            $table->string('meta_title', 180)->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->string('meta_keywords', 500)->nullable();
            $table->boolean('is_published')->default(false)->index();
            $table->timestamps();
        });
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 180)->unique();
            $table->longText('content');
            $table->timestamps();
        });
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 254);
            $table->string('phone', 20)->nullable();
            $table->string('subject', 180);
            $table->text('message');
            $table->string('status', 16)->default('pending')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('blogs');
        Schema::table('settings', fn (Blueprint $table) => $table->dropColumn([
            'site_name', 'site_url', 'seo_title', 'seo_description', 'seo_keywords',
            'phone', 'whatsapp', 'support_email', 'facebook_url', 'instagram_url',
            'x_url', 'linkedin_url', 'youtube_url',
        ]));
    }
};
