<?php
namespace Tests\Feature;

use App\Models\Advice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdviceAndAccountStatusTest extends TestCase
{
    use RefreshDatabase;
    private function account(string $role = 'user'): User {
        return User::create(['name'=>'Test '.$role,'phone'=>$role==='admin'?'1234567890':'9876543210',
            'password'=>'654321','role'=>$role]);
    }
    public function test_inactive_account_cannot_login_or_use_existing_token_and_can_reactivate(): void {
        $user=$this->account();
        $token=$user->createToken('test')->plainTextToken;
        $user->update(['is_active'=>false]);
        $this->postJson('/api/login',['phone'=>$user->phone,'password'=>'654321'])
            ->assertForbidden()->assertJsonPath('message','This account is inactive. Contact support.');
        $this->withToken($token)->getJson('/api/me')->assertForbidden();
        $this->assertSame(0,$user->tokens()->count());
        $user->update(['is_active'=>true]);
        $this->postJson('/api/login',['phone'=>$user->phone,'password'=>'654321'])->assertOk();
    }
    public function test_admin_deactivation_revokes_sessions_and_cannot_target_admin(): void {
        $user=$this->account(); $user->createToken('phone'); $admin=$this->account('admin');
        $this->actingAs($admin)->patch('/admin/accounts/'.$user->id.'/active',['is_active'=>0])->assertRedirect();
        $this->assertFalse($user->fresh()->is_active); $this->assertSame(0,$user->tokens()->count());
        $this->patch('/admin/accounts/'.$user->id.'/active',['is_active'=>1])->assertRedirect();
        $this->assertTrue($user->fresh()->is_active);
        $this->patch('/admin/accounts/'.$admin->id.'/active',['is_active'=>0])->assertNotFound();
        $this->patch('/admin/accounts/'.$user->id.'/active',['is_active'=>'bad'])->assertSessionHasErrors('is_active');
        $this->get('/admin/accounts?status=inactive')->assertOk()->assertDontSee('Test user');
    }
    public function test_advice_requires_auth_validates_and_does_not_trust_owner_or_status(): void {
        $this->postJson('/api/advice',['subject'=>'Hello','message'=>'Useful suggestion.'])->assertUnauthorized();
        $user=$this->account(); Sanctum::actingAs($user,['*']);
        $this->postJson('/api/advice',['subject'=>'x','message'=>'short'])->assertUnprocessable()->assertJsonStructure(['data'=>['subject','message']]);
        $this->postJson('/api/advice',['subject'=>'A useful idea','message'=>'Please add a useful feature.',
            'user_id'=>999,'status'=>'resolved','admin_note'=>'forged'])->assertCreated();
        $item=Advice::firstOrFail(); $this->assertEquals($user->id,$item->user_id);
        $this->assertSame('new',$item->status); $this->assertNull($item->admin_note);
        $this->get('/admin/advice')->assertRedirect('/admin/login');
    }
    public function test_advice_is_throttled_by_account_and_read_only_sessions_cannot_submit(): void {
        $user=$this->account(); Sanctum::actingAs($user,['profile:read']);
        $this->postJson('/api/advice',['subject'=>'A useful idea','message'=>'Please add a useful feature.'])->assertForbidden();
        Sanctum::actingAs($user,['*']);
        for($i=0;$i<4;$i++) $this->postJson('/api/advice',['subject'=>'A useful idea','message'=>'Please add a useful feature.'])->assertCreated();
        $this->postJson('/api/advice',['subject'=>'A useful idea','message'=>'Please add a useful feature.'])->assertStatus(429);
    }
    public function test_admin_can_review_and_filter_advice_and_html_is_escaped(): void {
        $user=$this->account();
        $item=new Advice(['subject'=>'Example suggestion','message'=>'<script>alert(1)</script>','status'=>'new']);
        $item->user()->associate($user);$item->save();
        $this->actingAs($this->account('admin'));
        $this->get('/admin/advice?q=Example&role=user&status=new')->assertOk()->assertSee('Example suggestion')->assertDontSee('<script>alert(1)</script>',false);
        $this->patch('/admin/advice/'.$item->id,['status'=>'planned','admin_note'=>'Consider next release'])->assertRedirect();
        $this->assertSame('planned',$item->fresh()->status);
        $this->get('/admin/advice?status=new')->assertDontSee('Example suggestion');
        $this->get('/admin/advice?status=planned')->assertSee('Example suggestion');
        $this->patch('/admin/advice/'.$item->id,['status'=>'invalid'])->assertSessionHasErrors('status');
    }
    public function test_readable_password_is_encrypted_hidden_and_kept_in_sync(): void {
        $user=$this->account();
        $this->assertSame('654321',$user->fresh()->show_password);
        $this->assertNotSame('654321',\Illuminate\Support\Facades\DB::table('users')->where('id',$user->id)->value('show_password'));
        $this->assertArrayNotHasKey('show_password',$user->toArray());
        $user->update(['password'=>'123456']);
        $this->assertSame('123456',$user->fresh()->show_password);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('123456',$user->fresh()->password));
        $user->update(['name'=>'Updated name']);
        $this->assertSame('123456',$user->fresh()->show_password);
        $this->assertNull($this->account('admin')->fresh()->show_password);
    }
}
