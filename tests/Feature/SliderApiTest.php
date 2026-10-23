<?php

namespace Tests\Feature;

use App\Models\Slider;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SliderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_multiple_sliders_and_delete_one(): void
    {
        Storage::fake('public');
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $png = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lWQAAAAASUVORK5CYII=';
        $images = [
            UploadedFile::fake()->createWithContent('one.png', base64_decode($png)),
            UploadedFile::fake()->createWithContent('two.png', base64_decode($png)),
        ];
        $this->get('/admin/sliders')->assertRedirect('/admin/login');
        $this->actingAs($admin, 'web')->post('/admin/sliders', [
            'images' => $images, 'title' => 'Welcome',
        ])->assertRedirect();
        $this->assertDatabaseCount('sliders', 2);
        $slider = Slider::firstOrFail();
        $this->get('/api/sliders/'.$slider->id.'/image')->assertOk();
        $this->getJson('/api/sliders')->assertOk()->assertJsonCount(2, 'data');
        $this->patch('/admin/sliders/'.$slider->id, [
            'title' => 'Updated', 'sort_order' => 3,
        ])->assertRedirect();
        $this->assertSame('Updated', $slider->refresh()->title);
        $this->delete('/admin/sliders/'.$slider->id)->assertRedirect();
        $this->assertDatabaseCount('sliders', 1);
        Storage::disk('public')->assertMissing($slider->image_path);
    }

    public function test_six_digit_registration_and_setting_seeder_preserves_changes(): void
    {
        $this->postJson('/api/user/create', ['name' => 'Invalid', 'phone' => '12345',
            'password' => '123456'])->assertUnprocessable();
        $this->postJson('/api/user/create', ['name' => 'Invalid', 'phone' => '9876543210',
            'password' => 'password123'])->assertUnprocessable();
        $created = $this->postJson('/api/user/create', [
            'name' => 'Akash', 'phone' => '+919876543210', 'password' => '123456',
        ])->assertCreated()->assertJsonPath('data.user.phone', '9876543210');
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/',
            $created->json('data.user.invite_code'));
        $this->seed(SettingSeeder::class);
        \App\Models\Setting::current()->update(['app_name' => 'Custom']);
        $this->seed(SettingSeeder::class);
        $this->assertSame('Custom', \App\Models\Setting::current()->app_name);
    }
}
