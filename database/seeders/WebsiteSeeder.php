<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class WebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $setting = Setting::current();
        if (!$setting->site_name) $setting->update([
            'site_name' => 'IP Tracker',
            'seo_title' => 'IP Tracker — Your places, clearly connected',
            'seo_description' => 'View your phone location, understand time spent at each place and connect members with IP Tracker for Android.',
            'seo_keywords' => 'IP Tracker, Android location, location history, stay time',
        ]);
        Page::firstOrCreate(['slug' => 'privacy-policy'], ['title' => 'Privacy Policy', 'content' =>
            '<h2>Your location and your account</h2><p>IP Tracker uses the location permission you grant to show your phone location and record changes. Your account includes your name, phone number, password hash, device name and login records.</p><h2>Connected accounts</h2><p>A member who joins with a user invite code shares location records with that connected user. Administrators can manage accounts and location records. Only join an account you trust.</p><h2>Background tracking and offline records</h2><p>When enabled, background tracking uses an ongoing Android notification. Offline changes are stored on the phone and uploaded when a connection is available. You can manage permissions in Android settings or sign out to stop tracking.</p><h2>Accuracy and retention</h2><p>Location and address accuracy depend on your device, permissions, signal and map providers. Contact support for questions about your data, correction or deletion. Records remain until removed by an administrator or the account is deleted.</p><h2>Website contact forms</h2><p>We store the details you submit so support can review your request. Contact messages are not published on the website. Third-party services may process map and address requests.</p>']);
        Page::firstOrCreate(['slug' => 'terms-and-conditions'], ['title' => 'Terms and Conditions', 'content' =>
            '<h2>Using IP Tracker</h2><p>Use the app only for your own device or with the informed permission of the device owner. Keep your password private and use invite codes only with people you trust.</p><h2>Location information</h2><p>Locations, addresses and stay times are estimates based on the readings available to the phone. The service is not an emergency service and must not be relied on for safety-critical decisions.</p><h2>Availability</h2><p>Network interruptions, device restrictions and maintenance may delay updates. Features and service availability may change. Contact support for assistance or questions about these terms.</p>']);
    }
}
