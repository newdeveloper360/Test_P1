<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FiltersAndAssetsTest extends TestCase
{
    use RefreshDatabase;

    private function owner(): User
    {
        $user = User::create(['name' => 'Owner', 'phone' => '9765551000', 'password' => '123456']);
        $this->withToken($user->createToken('fixture')->plainTextToken);

        return $user;
    }

    private function visit(User $user, string $at, string $address): void
    {
        $user->locations()->create(['latitude' => 28.6, 'longitude' => 77.2, 'captured_at' => $at, 'full_address' => $address]);
    }

    public function test_member_name_phone_filters_are_combined_and_owner_scoped(): void
    {
        $owner = $this->owner();
        User::create(['name' => 'Alice Example', 'phone' => '9765551111', 'password' => '123456', 'role' => 'member', 'invited_by_user_id' => $owner->id]);
        User::create(['name' => 'Alice Other', 'phone' => '9765552222', 'password' => '123456', 'role' => 'member', 'invited_by_user_id' => $owner->id]);
        User::create(['name' => 'Alice Outsider', 'phone' => '9765553111', 'password' => '123456', 'role' => 'member']);
        $this->getJson('/api/members?name=Alice&phone=111')->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.data.0.name', 'Alice Example');
        $this->getJson('/api/members?phone=abc')->assertUnprocessable();
        $this->getJson('/api/members?phone=0')->assertOk()->assertJsonPath('data.total', 0);
    }

    public function test_date_time_filters_use_local_zone_and_include_the_selected_end_minute(): void
    {
        $user = $this->owner();
        $this->visit($user, '2026-10-25T04:29:59Z', 'Before');
        $this->visit($user, '2026-10-25T04:30:00Z', 'Start');
        $this->visit($user, '2026-10-25T05:00:45Z', 'End minute');
        $this->visit($user, '2026-10-25T05:01:00Z', 'After');
        $query = http_build_query(['from' => '2026-10-25T10:00', 'to' => '2026-10-25T10:30', 'timezone' => 'Asia/Kolkata']);
        $this->getJson('/api/locations?'.$query)->assertOk()->assertJsonPath('data.total', 2)
            ->assertJsonPath('data.data.0.full_address', 'End minute')->assertJsonPath('data.data.1.full_address', 'Start');
        $this->getJson('/api/locations?'.http_build_query(['from' => '2026-10-25T04:30:00Z', 'to' => '2026-10-25T05:00:45Z']))
            ->assertOk()->assertJsonPath('data.total', 2);
    }

    public function test_invalid_ranges_and_zones_return_field_validation_errors(): void
    {
        $this->owner();
        $this->getJson('/api/locations?from=tomorrow')->assertUnprocessable()->assertJsonValidationErrors('from', 'data');
        $this->getJson('/api/locations?timezone=Invalid/Zone')->assertUnprocessable()->assertJsonValidationErrors('timezone', 'data');
        $this->getJson('/api/locations?from=2026-10-25T11:00&to=2026-10-25T10:00')->assertUnprocessable()->assertJsonValidationErrors('to', 'data');
    }

    public function test_member_history_filters_preserve_owner_authorization(): void
    {
        $owner = $this->owner();
        $member = User::create(['name' => 'Member', 'phone' => '9765551001', 'password' => '123456', 'role' => 'member', 'invited_by_user_id' => $owner->id]);
        $this->visit($member, '2026-10-25T10:00:00Z', 'Selected');
        $this->visit($member, '2026-10-26T10:00:00Z', 'Outside');
        $this->getJson('/api/members/'.$member->id.'/locations?from=2026-10-25&to=2026-10-25')->assertOk()->assertJsonPath('data.total', 1);
        $other = User::create(['name' => 'Other', 'phone' => '9765551002', 'password' => '123456']);
        $this->app->make('auth')->forgetGuards();
        $this->withToken($other->createToken('fixture')->plainTextToken)->getJson('/api/members/'.$member->id.'/locations?from=2026-10-25')->assertNotFound();
    }

    public function test_filtered_pagination_retains_member_filters(): void
    {
        $owner = $this->owner();
        User::factory()->count(101)->create(['name' => 'Matching', 'role' => 'member', 'invited_by_user_id' => $owner->id]);
        $response = $this->getJson('/api/members?name=Matching')->assertOk()->assertJsonPath('data.total', 101);
        $this->assertStringContainsString('name=Matching', $response->json('data.next_page_url'));
    }

    public function test_admin_slider_page_contact_and_datetime_filters_work(): void
    {
        $admin = User::create(['name' => 'Admin', 'phone' => '1234567890', 'password' => '123456', 'role' => 'admin']);
        $this->actingAs($admin, 'web');
        Slider::create(['title' => 'Welcome', 'image_path' => 'fixture.png', 'sort_order' => 1, 'is_active' => true]);
        Slider::create(['title' => 'Welcome draft', 'image_path' => 'fixture2.png', 'sort_order' => 2, 'is_active' => false]);
        $this->get('/admin/sliders?q=Welcome&status=active')->assertOk()->assertViewHas('sliders', fn ($rows) => $rows->total() === 1);
        Page::create(['title' => 'Privacy policy', 'slug' => 'privacy-policy', 'content' => '<p>Privacy.</p>']);
        $this->get('/admin/pages?q=privacy-policy')->assertOk()->assertViewHas('pages', fn ($rows) => $rows->total() === 1);
        ContactMessage::create(['name' => 'Customer', 'email' => 'customer@example.test', 'subject' => 'Location help', 'message' => 'Help please', 'status' => 'pending']);
        $this->get('/admin/contacts?q=Location&status=pending')->assertOk()->assertViewHas('messages', fn ($rows) => $rows->total() === 1);
        $user = User::create(['name' => 'Owner', 'phone' => '9765551000', 'password' => '123456']);
        $this->visit($user, '2026-10-25T04:30:00Z', 'Delhi street');
        $this->get('/admin/accounts/'.$user->id.'/locations?'.http_build_query(['from' => '2026-10-25T10:00', 'to' => '2026-10-25T10:00', 'timezone' => 'Asia/Kolkata']))
            ->assertOk()->assertViewHas('locations', fn ($rows) => $rows->total() === 1);
    }

    public function test_web_pages_use_canonical_local_assets_and_roboto(): void
    {
        $this->get('/')->assertOk()->assertSee('assets/frontend/css/roboto.css', false)->assertSee('assets/frontend/images/phone-preview.svg', false);
        $this->get('/admin/login')->assertOk()->assertSee('assets/backend/css/roboto.css', false);
        foreach (['frontend', 'backend'] as $surface) {
            foreach (['css/roboto.css', 'css/bootstrap.min.css', 'js/bootstrap.bundle.min.js', 'fonts/roboto-variable.ttf', 'fonts/OFL.txt'] as $file) {
                $this->assertFileExists(public_path('assets/'.$surface.'/'.$file));
            }
        }
        $css = file_get_contents(public_path('assets/frontend/css/website.css'));
        $this->assertStringNotContainsString('fonts.googleapis.com', $css);
        $this->assertStringStartsWith(':root', ltrim($css));
    }
}
