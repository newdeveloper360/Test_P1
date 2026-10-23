<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_invite_code_connects_member_and_limits_history_to_owner(): void
    {
        $ownerResponse = $this->postJson('/api/user/create', [
            'name' => 'Owner', 'phone' => '9876543210', 'password' => '123456', 'role' => 'user',
        ])->assertCreated();
        $code = $ownerResponse->json('data.user.invite_code');
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
        $this->postJson('/api/user/create', [
            'name' => 'Member', 'phone' => '9876543211', 'password' => '123456',
            'role' => 'member', 'invite_code' => '000000',
        ])->assertUnprocessable();
        $memberResponse = $this->postJson('/api/user/create', [
            'name' => 'Member', 'phone' => '9876543211', 'password' => '123456',
            'role' => 'member', 'invite_code' => $code,
        ])->assertCreated()->assertJsonPath('data.user.role', 'member');
        $memberId = $memberResponse->json('data.user.id');
        $this->assertSame($ownerResponse->json('data.user.id'),
            $memberResponse->json('data.user.invited_by_user_id'));

        $memberToken = $memberResponse->json('data.token');
        $this->withToken($memberToken)->postJson('/api/locations', [
            'latitude' => 28.6139, 'longitude' => 77.2090,
            'city' => 'New Delhi', 'captured_at' => '2026-10-15T06:00:00Z',
        ])->assertCreated();
        $this->app->make('auth')->forgetGuards();
        $this->withToken($memberToken)->getJson('/api/members')->assertForbidden();
        $this->app->make('auth')->forgetGuards();
        $this->withToken($ownerResponse->json('data.token'))->getJson('/api/members')
            ->assertOk()->assertJsonPath('data.data.0.id', $memberId);
        $this->withToken($ownerResponse->json('data.token'))
            ->getJson("/api/members/{$memberId}/locations")
            ->assertOk()->assertJsonPath('data.data.0.city', 'New Delhi');

        $other = User::create(['name' => 'Other', 'phone' => '9876543212',
            'password' => '123456', 'role' => 'user', 'invite_code' => '654321']);
        $this->app->make('auth')->forgetGuards();
        $this->withToken($other->createToken('test')->plainTextToken)
            ->getJson("/api/members/{$memberId}/locations")->assertNotFound();
    }

    public function test_profile_password_login_history_and_web_admin_member_totals(): void
    {
        $this->seed(AdminUserSeeder::class);
        $owner = $this->postJson('/api/user/create', [
            'name' => 'Owner', 'phone' => '9876543210', 'password' => '123456', 'role' => 'user',
        ])->assertCreated();
        $member = $this->postJson('/api/user/create', [
            'name' => 'Member', 'phone' => '9876543211', 'password' => '123456',
            'role' => 'member', 'invite_code' => $owner->json('data.user.invite_code'),
        ])->assertCreated();
        $token = $member->json('data.token');
        $this->withToken($token)->getJson('/api/login-history')
            ->assertOk()->assertJsonPath('data.total', 1);
        $this->withToken($token)->patchJson('/api/profile', [
            'name' => 'Updated Member', 'phone' => '9876543211',
        ])->assertOk()->assertJsonPath('data.user.name', 'Updated Member');
        $this->withToken($token)->patchJson('/api/password', [
            'current_password' => 'wrong', 'password' => '654321',
            'password_confirmation' => '654321',
        ])->assertUnprocessable();
        $this->withToken($token)->patchJson('/api/password', [
            'current_password' => '123456', 'password' => '654321',
            'password_confirmation' => '654321',
        ])->assertOk();
        $this->postJson('/api/login', [
            'phone' => '9876543211', 'password' => '654321',
        ])->assertOk();

        $admin = User::where('role', 'admin')->firstOrFail();
        $this->post('/admin/login', ['phone' => $admin->phone,
            'password' => '112233'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk()->assertSee('Members')->assertSee('1');
        $this->get('/admin/accounts')->assertOk()->assertSee('Updated Member');
    }
}

