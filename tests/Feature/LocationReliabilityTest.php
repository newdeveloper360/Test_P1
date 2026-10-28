<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\LocationIdentity;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LocationReliabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_clear_also_discards_millisecond_offline_replays(): void
    {
        $this->travelTo(Carbon::parse('2026-10-26T10:00:00.500000Z'));
        $user = $this->signIn();
        $data = $this->fix('Street A', '10:00:00.400000');
        $data['captured_at'] = '2026-10-26T10:00:00.400000Z';
        $this->postJson('/api/location-fixes', $data)->assertCreated();
        $admin = User::create(['name' => 'Admin', 'phone' => '1234567890', 'password' => '123456', 'role' => 'admin']);
        $this->actingAs($admin, 'web')->delete('/admin/accounts/'.$user->id.'/locations')->assertRedirect();
        auth('web')->logout();
        $this->app->make('auth')->forgetGuards();
        $this->withToken($user->createToken('after-clear')->plainTextToken)->postJson('/api/location-fixes', $data)
            ->assertOk()->assertJsonPath('data.discarded', true);
        $this->assertDatabaseCount('location_fixes', 0);
        $this->assertDatabaseCount('user_locations', 0);
    }

    public function test_phone_millisecond_capture_retry_is_idempotent(): void
    {
        $this->signIn();
        $data = $this->fix('Street A', '10:00:00.123456');
        $this->postJson('/api/location-fixes', $data)->assertCreated();
        $this->postJson('/api/location-fixes', $data)->assertCreated();
        $this->assertDatabaseCount('location_fixes', 1);
        $this->assertDatabaseCount('user_locations', 1);
    }

    private function signIn(): User
    {
        $user = User::create(['name' => 'Audit', 'phone' => '9876543210', 'password' => '123456']);
        $this->withToken($user->createToken('test')->plainTextToken);

        return $user;
    }

    private function fix(string $address, string $time, float $latitude = 28.6): array
    {
        return ['client_fix_id' => (string) Str::uuid(), 'latitude' => $latitude, 'longitude' => 77.2, 'accuracy_meters' => 5,
            'full_address' => $address, 'street' => $address, 'city' => 'Delhi', 'country' => 'India', 'captured_at' => '2026-10-25T'.$time.'Z', 'record_visit' => true];
    }

    public function test_same_address_updates_live_capture_without_duplicate_visit(): void
    {
        $this->signIn();
        $a = $this->fix('Street A', '10:00:00');
        $b = $this->fix('Street A', '10:00:15', 28.62);
        $this->postJson('/api/locations', $a)->assertCreated();
        $this->postJson('/api/locations', $b)->assertOk();
        $this->assertDatabaseCount('user_locations', 1);
        $this->assertDatabaseCount('location_fixes', 2);
        $this->getJson('/api/locations/latest')->assertJsonPath('data.location.client_fix_id', $b['client_fix_id'])
            ->assertJsonPath('data.visit.captured_at', '2026-10-25T10:00:00.000000Z');
    }

    public function test_country_or_city_only_cannot_merge_distant_places(): void
    {
        $this->assertFalse(LocationIdentity::same(['city' => 'Delhi', 'country' => 'India', 'latitude' => 28.6, 'longitude' => 77.2],
            ['full_address' => 'Mumbai road', 'country' => 'India', 'latitude' => 19.0, 'longitude' => 72.8]));
        $this->assertFalse(LocationIdentity::same(['city' => 'Delhi', 'full_address' => 'Delhi', 'latitude' => 28.6, 'longitude' => 77.2],
            ['city' => 'Delhi', 'full_address' => 'Delhi', 'latitude' => 28.7, 'longitude' => 77.2]));
    }

    public function test_earlier_offline_arrival_recomputes_previous_stay(): void
    {
        $u = $this->signIn();
        $this->postJson('/api/locations', $this->fix('Street B', '10:00:00'))->assertCreated();
        $this->postJson('/api/locations', $this->fix('Street A', '10:20:00'))->assertCreated();
        $this->postJson('/api/locations', $this->fix('Street A', '10:10:00'))->assertOk();
        $visits = $u->locations()->orderBy('captured_at')->get();
        $this->assertCount(2, $visits);
        $this->assertSame(600, $visits[0]->stay_seconds);
        $this->assertSame('10:10:00', $visits[1]->captured_at->format('H:i:s'));
    }

    public function test_late_intervening_visit_splits_previously_deduplicated_visit(): void
    {
        $u = $this->signIn();
        $this->postJson('/api/locations', $this->fix('Street A', '10:00:00'))->assertCreated();
        $this->postJson('/api/locations', $this->fix('Street A', '10:20:00'))->assertOk();
        $this->postJson('/api/locations', $this->fix('Street B', '10:10:00'))->assertCreated();
        $visits = $u->locations()->orderBy('captured_at')->get();
        $this->assertSame(['Street A', 'Street B', 'Street A'], $visits->pluck('full_address')->all());
        $this->assertSame([600, 600, null], $visits->pluck('stay_seconds')->all());
    }

    public function test_duplicate_capture_and_enrichment_are_account_scoped_and_immutable(): void
    {
        $u = $this->signIn();
        $raw = $this->fix('Street A', '10:00:00');
        $raw['record_visit'] = false;
        unset($raw['full_address'],$raw['street']);
        $this->postJson('/api/locations', $raw)->assertCreated();
        $enriched = $raw + ['street' => 'Street A', 'full_address' => 'Street A'];
        $enriched['record_visit'] = true;
        $this->postJson('/api/locations', $enriched)->assertCreated();
        $this->postJson('/api/locations', $enriched)->assertCreated();
        $this->assertDatabaseCount('location_fixes', 1);
        $this->assertDatabaseCount('user_locations', 1);
        $changed = $enriched;
        $changed['latitude'] = 20;
        $this->postJson('/api/locations', $changed)->assertConflict();
        $other = User::create(['name' => 'Other', 'phone' => '9876543211', 'password' => '123456']);
        $this->app->make('auth')->forgetGuards();
        $this->withToken($other->createToken('test')->plainTextToken)->postJson('/api/locations', $enriched)->assertCreated();
        $this->assertDatabaseCount('location_fixes', 2);
    }

    public function test_future_and_impossible_accuracy_are_rejected_but_pending_raw_is_durable(): void
    {
        $this->signIn();
        $data = $this->fix('Street A', '10:00:00');
        $data['captured_at'] = '2099-01-01T00:00:00Z';
        $this->postJson('/api/locations', $data)->assertUnprocessable();
        $data['captured_at'] = '2026-10-25T10:00:00Z';
        $data['accuracy_meters'] = 99999;
        $this->postJson('/api/locations', $data)->assertUnprocessable();
        $data['accuracy_meters'] = 500;
        $this->postJson('/api/locations', $data)->assertCreated()->assertJsonPath('data.location.quality_status', 'approximate');
        $this->assertDatabaseCount('location_fixes', 1);
        $this->assertDatabaseCount('user_locations', 0);
    }

    public function test_tracking_gap_does_not_claim_observed_continuous_coverage(): void
    {
        $u = $this->signIn();
        $this->postJson('/api/locations', $this->fix('Street A', '10:00:00'))->assertCreated();
        $this->postJson('/api/locations', $this->fix('Street A', '11:00:00'))->assertOk();
        $this->assertSame('unknown', $u->locations()->first()->coverage_status);
    }

    public function test_members_pagination_and_inactive_state_are_explicit(): void
    {
        $u = $this->signIn();
        User::factory()->count(101)->create(['role' => 'member', 'invited_by_user_id' => $u->id, 'is_active' => false]);
        $this->getJson('/api/members')->assertOk()->assertJsonCount(100, 'data.data')->assertJsonPath('data.total', 101)->assertJsonPath('data.data.0.is_active', false);
        $this->getJson('/api/members?page=2')->assertOk()->assertJsonCount(1, 'data.data');
    }
}
