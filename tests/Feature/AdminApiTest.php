<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_web_admin_can_manage_accounts_and_blocked_user_cannot_use_app(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('phone', '1234567890')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $user = User::create(['name' => 'Akash', 'phone' => '9876543210', 'password' => '123456']);
        $userToken = $user->createToken('android')->plainTextToken;
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/admin/login', ['phone' => $user->phone,
            'password' => '123456'])->assertSessionHasErrors('phone');
        $this->postJson('/api/login', ['phone' => $admin->phone,
            'password' => '112233'])->assertForbidden();
        $adminToken = $admin->createToken('android')->plainTextToken;
        $this->app->make('auth')->forgetGuards();
        $this->withToken($adminToken)->getJson('/api/me')->assertForbidden();
        $this->app->make('auth')->forgetGuards();
        $this->post('/admin/login', ['phone' => $admin->phone,
            'password' => '112233'])->assertRedirect('/admin');
        $this->get('/admin/accounts')->assertOk()->assertSee('Akash');
        $this->patch("/admin/accounts/{$user->id}/block")->assertRedirect();
        $this->assertTrue($user->refresh()->is_blocked);
        $this->postJson('/api/login', ['phone' => '9876543210', 'password' => '123456'])
            ->assertForbidden()->assertJsonPath('message', 'This account is blocked.');
        $this->app->make('auth')->forgetGuards();
        $this->withToken($userToken)->getJson('/api/me')->assertForbidden();
    }

    public function test_unchanged_location_is_not_saved_again(): void
    {
        $user = User::create(['name' => 'Akash', 'phone' => '9876543210', 'password' => 'password123']);
        $token = $user->createToken('android')->plainTextToken;
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.6139, 'longitude' => 77.2090,
        ])->assertCreated();
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.61391, 'longitude' => 77.20901,
        ])->assertOk()->assertJsonPath('data.unchanged', true);
        $this->assertDatabaseCount('user_locations', 1);
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.6150, 'longitude' => 77.2090,
        ])->assertCreated();
        $this->assertDatabaseCount('user_locations', 2);
    }

    public function test_address_change_is_saved_even_nearby_and_same_address_is_not_saved_far_away(): void
    {
        $user = User::create(['name' => 'Akash', 'phone' => '9876543210', 'password' => 'password123']);
        $token = $user->createToken('android')->plainTextToken;
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.6139, 'longitude' => 77.2090,
            'place' => 'Area A', 'city' => 'New Delhi', 'postal_code' => '110001',
        ])->assertCreated();
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.6150, 'longitude' => 77.2090,
            'place' => 'Area A', 'city' => 'New Delhi', 'postal_code' => '110001',
        ])->assertOk()->assertJsonPath('data.unchanged', true);
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.61391, 'longitude' => 77.20901,
            'place' => 'Area B', 'city' => 'New Delhi', 'postal_code' => '110001',
        ])->assertCreated();
        $this->assertDatabaseCount('user_locations', 2);
    }

    public function test_offline_locations_keep_capture_order_and_saved_stay_time(): void
    {
        $user = User::create(['name' => 'Traveler', 'phone' => '9876543210',
            'password' => 'password123']);
        $token = $user->createToken('android')->plainTextToken;
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.60, 'longitude' => 77.20,
            'captured_at' => '2026-10-16T10:00:00Z',
        ])->assertCreated();
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.62, 'longitude' => 77.22,
            'captured_at' => '2026-10-16T10:10:00Z',
        ])->assertCreated();
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.61, 'longitude' => 77.21,
            'captured_at' => '2026-10-16T10:05:00Z',
        ])->assertCreated();

        $history = $this->withToken($token)->getJson('/api/locations')->assertOk();
        $history->assertJsonPath('data.data.0.stay_seconds', null)
            ->assertJsonPath('data.data.1.stay_seconds', 300)
            ->assertJsonPath('data.data.2.stay_seconds', 300);
        $this->assertDatabaseCount('user_locations', 3);
    }
}
