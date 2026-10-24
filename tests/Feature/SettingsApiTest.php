<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_branding_and_maintenance_blocks_members(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $member = User::create(['name' => 'Member', 'phone' => '9876543210',
            'password' => '123456', 'role' => 'member']);
        $memberToken = $member->createToken('android')->plainTextToken;
        $this->actingAs($admin, 'web')->patch('/admin/settings', [
            'app_name' => 'Tracker Plus', 'banner_text' => 'Service update',
            'maintenance_mode' => true, 'maintenance_message' => 'Back soon',
        ])->assertRedirect();
        $this->assertTrue(\App\Models\Setting::current()->maintenance_mode);
        $this->getJson('/api/settings')->assertOk()
            ->assertJsonPath('data.app_name', 'Tracker Plus');
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->postJson('/api/login', ['phone' => $member->phone,
            'password' => '123456'])->assertStatus(503);
        $this->app->make('auth')->forgetGuards();
        $this->withToken($memberToken)->getJson('/api/me')->assertStatus(503);
        $this->postJson('/api/user/create', ['name' => 'New', 'phone' => '9876543211',
            'password' => '234567'])->assertStatus(503);
        $this->postJson('/api/login', ['phone' => $admin->phone,
            'password' => '112233'])->assertForbidden();
    }

    public function test_admin_can_publish_android_version_and_update_link(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($admin, 'web')->patch('/admin/settings', [
            'app_name' => 'IP Tracker',
            'android_version_code' => 2,
            'android_update_url' => 'https://example.com/app.apk',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->getJson('/api/settings')->assertOk()
            ->assertJsonPath('data.android_version_code', 2)
            ->assertJsonPath('data.android_update_url', 'https://example.com/app.apk');
        $this->actingAs($admin, 'web')->patch('/admin/settings', [
            'app_name' => 'IP Tracker', 'android_version_code' => 0,
        ])->assertSessionHasErrors('android_version_code');
    }

    public function test_login_and_registration_are_throttled_by_phone(): void
    {
        for ($i = 0; $i < 5; $i++)
            $this->postJson('/api/login', ['phone' => '9876543210',
                'password' => '999999'])->assertUnauthorized();
        $this->postJson('/api/login', ['phone' => '9876543210',
            'password' => '999999'])->assertStatus(429);
        for ($i = 0; $i < 3; $i++)
            $this->postJson('/api/user/create', ['name' => '', 'phone' => '9898989898',
                'password' => '123456'])->assertUnprocessable();
        $this->postJson('/api/user/create', ['name' => '', 'phone' => '9898989898',
            'password' => '123456'])->assertStatus(429);
    }

    public function test_admin_can_upload_and_serve_logo(): void
    {
        Storage::fake('public');
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lWQAAAAASUVORK5CYII=';
        $response = $this->actingAs($admin, 'web')->post('/admin/settings/image/logo', [
            'image' => UploadedFile::fake()->createWithContent('logo.png', base64_decode($png)),
        ])->assertRedirect();
        $this->assertStringStartsWith('/api/settings/image/logo?v=',
            \App\Models\Setting::current()->logo_url);
        $this->get('/api/settings/image/logo')->assertOk();
    }
}
