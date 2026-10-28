<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\Slider;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocationMigrationAndMediaTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_visits_are_preserved_and_backfilled_by_migration(): void
    {
        Schema::drop('location_fixes');
        Schema::table('user_locations', fn ($t) => $t->dropColumn(['last_seen_at', 'coverage_status']));
        $u = User::create(['name' => 'Old', 'phone' => '9876543210', 'password' => '123456']);
        $id = DB::table('user_locations')->insertGetId(['user_id' => $u->id, 'latitude' => 28.6, 'longitude' => 77.2, 'captured_at' => '2026-10-25 10:00:00', 'full_address' => 'Street A', 'created_at' => now(), 'updated_at' => now()]);
        (require database_path('migrations/2026_10_26_100000_separate_live_fixes_from_visits.php'))->up();
        $this->assertDatabaseHas('user_locations', ['id' => $id, 'user_id' => $u->id]);
        $this->assertDatabaseHas('location_fixes', ['visit_id' => $id, 'user_id' => $u->id, 'full_address' => 'Street A', 'record_visit' => true]);
        $this->assertDatabaseCount('user_locations', 1);
    }

    public function test_new_branding_and_slider_images_survive_missing_local_files(): void
    {
        Storage::fake('public');
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web');
        $bytes = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lWQAAAAASUVORK5CYII=');
        $this->post('/admin/settings/image/logo', ['image' => UploadedFile::fake()->createWithContent('logo.png', $bytes)])->assertRedirect();
        $this->post('/admin/sliders', ['images' => [UploadedFile::fake()->createWithContent('slide.png', $bytes)]])->assertRedirect();
        Storage::disk('public')->delete(Setting::current()->logo_path);
        $slider = Slider::firstOrFail();
        Storage::disk('public')->delete($slider->image_path);
        $this->get('/api/settings/image/logo')->assertOk()->assertContent($bytes);
        $this->get('/api/sliders/'.$slider->id.'/image')->assertOk()->assertContent($bytes);
        $this->getJson('/api/settings')->assertJsonMissingPath('data.logo_data');
        $this->getJson('/api/sliders')->assertJsonMissingPath('data.0.image_data');
    }
}
