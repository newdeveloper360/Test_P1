<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_web_routes_create_update_block_and_delete_accounts(): void
    {
        $this->seed(AdminUserSeeder::class);
        $admin = User::where('role', 'admin')->firstOrFail();
        $this->post('/admin/login', ['phone' => $admin->phone,
            'password' => '112233'])->assertRedirect('/admin');

        $this->post('/admin/accounts', [
            'name' => 'Owner', 'phone' => '9876543210', 'password' => '123456',
            'role' => 'user',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $owner = User::where('phone', '9876543210')->firstOrFail();
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $owner->invite_code);
        $this->post('/admin/accounts', [
            'name' => 'Member', 'phone' => '9876543211', 'password' => '123456',
            'role' => 'member', 'invited_by_user_id' => $owner->id,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $member = User::where('phone', '9876543211')->firstOrFail();
        $this->assertSame($owner->id, $member->invited_by_user_id);
        $this->get('/admin/accounts/'.$owner->id)->assertOk()->assertSee('Member');
        $this->patch('/admin/accounts/'.$member->id, [
            'name' => 'Updated Member', 'phone' => '9876543211',
        ])->assertRedirect();
        $this->assertSame('Updated Member', $member->refresh()->name);
        $this->patch('/admin/accounts/'.$member->id.'/block')->assertRedirect();
        $this->assertTrue($member->refresh()->is_blocked);
        $this->delete('/admin/accounts/'.$member->id)->assertRedirect('/admin/accounts');
        $this->assertDatabaseMissing('users', ['id' => $member->id]);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->get('/admin/accounts')->assertRedirect('/admin/login');
    }

    public function test_non_admin_web_session_cannot_open_admin_pages(): void
    {
        $user = User::create(['name' => 'User', 'phone' => '9876543210',
            'password' => '123456', 'role' => 'user']);
        $this->actingAs($user, 'web')->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/accounts')->assertRedirect('/admin/login');
    }
}
