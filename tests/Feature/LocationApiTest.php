<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_and_login_use_phone_without_allowing_admin_signup(): void
    {
        $this->postJson('/api/user/create', [
            'name' => 'Akash',
            'phone' => '9876543210',
            'password' => '123456',
            'role' => 'admin',
            'is_blocked' => true,
        ])->assertUnprocessable();

        $response = $this->postJson('/api/user/create', [
            'name' => 'Akash', 'phone' => '9876543210', 'password' => '123456',
            'role' => 'user', 'is_blocked' => true,
        ]);

        $response->assertCreated()->assertJsonPath('data.user.role', 'user')
            ->assertJsonPath('data.user.is_blocked', false)
            ->assertJsonStructure(['success', 'message', 'data' => ['token', 'user' => ['id', 'created_at']]]);
        $user = User::firstOrFail();
        $this->assertTrue(Hash::check('123456', $user->password));
        $this->assertNotNull($user->created_at);
        $this->assertNull($response->json('data.user.password'));
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $response->json('data.user.invite_code'));

        $this->postJson('/api/login', [
            'phone' => '9876543210', 'password' => '123456',
        ])->assertOk()->assertJsonStructure(['data' => ['token']]);
        $this->postJson('/api/login', [
            'phone' => '9876543210', 'password' => 'incorrect-password',
        ])->assertUnauthorized();
    }

    public function test_locations_require_auth_and_are_saved_in_separate_columns(): void
    {
        $user = User::create([
            'name' => 'Akash', 'phone' => '9876543210', 'password' => '123456',
        ]);
        $other = User::create([
            'name' => 'Other', 'phone' => '9876543211', 'password' => '123456',
        ]);
        $payload = [
            'client_fix_id' => 'f0f32b4a-2817-49f6-a1aa-c8b227b6c243',
            'latitude' => 28.6139391,
            'longitude' => 77.2090212,
            'accuracy_meters' => 12.5,
            'full_address' => 'New Delhi, Delhi 110001, India',
            'place' => 'Connaught Place',
            'street' => 'Janpath',
            'city' => 'New Delhi',
            'district' => 'New Delhi',
            'state' => 'Delhi',
            'postal_code' => '110001',
            'country' => 'India',
            'device_name' => 'Example phone',
            'captured_at' => '2026-10-15T11:30:00+05:30',
        ];

        $this->postJson('/api/locations', $payload)->assertUnauthorized();
        $token = $user->createToken('test')->plainTextToken;
        $this->withToken($token)->postJson('/api/locations', $payload)->assertCreated();
        $this->withToken($token)->postJson('/api/locations', $payload)->assertCreated();
        $this->assertDatabaseCount('user_locations', 1);
        $this->assertDatabaseHas('user_locations', [
            'user_id' => $user->id,
            'city' => 'New Delhi',
            'district' => 'New Delhi',
            'postal_code' => '110001',
            'device_name' => 'Example phone',
            'captured_at' => '2026-10-15 06:00:00',
        ]);
        $this->withToken($token)->getJson('/api/locations/latest')
            ->assertOk()->assertJsonPath('data.location.city', 'New Delhi');
        $otherToken = $other->createToken('test')->plainTextToken;
        $this->app->make('auth')->forgetGuards();
        $this->withToken($otherToken)->getJson('/api/locations/latest')
            ->assertOk()->assertJsonPath('data.location', null);
    }

    public function test_blocked_user_cannot_login_or_upload_locations(): void
    {
        $user = User::create([
            'name' => 'Akash', 'phone' => '9876543210', 'password' => '123456',
        ]);
        $token = $user->createToken('test')->plainTextToken;
        $user->forceFill(['is_blocked' => true])->save();

        $this->postJson('/api/login', [
            'phone' => '9876543210', 'password' => '123456',
        ])->assertForbidden();
        $this->withToken($token)->postJson('/api/locations', [
            'latitude' => 28.6, 'longitude' => 77.2,
        ])->assertForbidden();
        $this->assertDatabaseCount('user_locations', 0);
    }
}

