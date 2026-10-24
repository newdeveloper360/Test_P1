<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Setting;
use App\Models\User;
use App\Support\CleanHtml;
use Database\Seeders\WebsiteSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class WebsiteTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'phone' => '1234567890', 'password' => '654321', 'role' => 'admin']);
    }

    private function image(): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('cover.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/lWQAAAAASUVORK5CYII='));
    }

    public function test_public_routes_and_seo_are_rendered_with_conditional_contacts(): void
    {
        $this->seed(WebsiteSeeder::class);
        foreach (['/', '/about', '/features', '/faq', '/contact', '/blog', '/pages/privacy-policy', '/pages/terms-and-conditions'] as $url) {
            $response = $this->get($url)->assertOk();
            $response->assertSee('name="description"', false)->assertSee('rel="canonical"', false)
                ->assertSee('property="og:title"', false)->assertSee('application/ld+json', false);
        }
        $this->get('/')->assertDontSee('wa.me')->assertDontSee('href="tel:', false);
        Setting::current()->update(['phone' => '+919876543210', 'whatsapp' => '919876543210',
            'youtube_url' => 'https://www.youtube.com/@example', 'support_email' => 'support@example.com']);
        $this->get('/')->assertSee('wa.me/919876543210', false)->assertSee('tel:+919876543210', false)
            ->assertSee('youtube.com/@example', false)->assertSee('support@example.com');
        $sitemap = $this->get('/sitemap.xml')->assertOk()->assertSee('/pages/privacy-policy', false);
        $xml = new \DOMDocument();
        $this->assertTrue($xml->loadXML($sitemap->getContent()));
        $this->assertGreaterThanOrEqual(8, $xml->getElementsByTagName('url')->length);
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin', false);
        $this->get('/not-a-page')->assertNotFound();
    }

    public function test_admin_blog_crud_validates_files_and_cleans_published_html(): void
    {
        $this->get('/admin/blogs')->assertRedirect('/admin/login');
        $this->actingAs($this->admin());
        $data = ['title' => 'My first story', 'short_description' => 'A useful introduction.',
            'content' => '<h2>Hello</h2><p onclick="alert(1)">Safe content</p><script>alert(1)</script><a href="javascript:alert(1)">Bad link</a>',
            'meta_title' => 'Story SEO title', 'meta_description' => 'Story SEO description',
            'meta_keywords' => 'location, app', 'is_published' => 1];
        $this->post('/admin/blogs', $data + ['featured_image' => UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')])
            ->assertSessionHasErrors('featured_image');
        $this->post('/admin/blogs', $data + ['featured_image' => $this->image()])
            ->assertRedirect('/admin/blogs')->assertSessionHasNoErrors();
        $blog = Blog::firstOrFail();
        $this->assertSame('my-first-story', $blog->slug);
        $this->assertStringNotContainsString('<script', $blog->content);
        $this->assertStringNotContainsString('onclick', $blog->content);
        $this->assertStringNotContainsString('javascript:', $blog->content);
        $this->get('/admin/blogs/'.$blog->id.'/edit')->assertOk()->assertSee('ckeditor.com/4.22.1', false);
        $this->get('/blog/'.$blog->slug)->assertOk()->assertSee('Story SEO title')->assertSee('Safe content');
        $this->get('/media/blog/'.$blog->id)->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->put('/admin/blogs/'.$blog->id, array_merge($data, ['title' => 'Updated story', 'slug' => 'updated-story', 'is_published' => 0]))
            ->assertSessionHasNoErrors();
        $this->get('/blog/updated-story')->assertNotFound();
        $this->delete('/admin/blogs/'.$blog->id)->assertRedirect();
        $this->assertDatabaseCount('blogs', 0);
    }

    public function test_page_crud_slug_uniqueness_and_sanitizer(): void
    {
        $this->actingAs($this->admin());
        $data = ['title' => 'Privacy', 'slug' => 'privacy', 'content' => '<p>Policy</p><iframe src="https://evil.test"></iframe>'];
        $this->post('/admin/pages', $data)->assertSessionHasNoErrors();
        $page = Page::firstOrFail();
        $this->assertStringNotContainsString('iframe', $page->content);
        $this->get('/pages/privacy')->assertOk()->assertSee('Policy');
        $this->post('/admin/pages', $data)->assertSessionHasErrors('slug');
        $this->put('/admin/pages/'.$page->id, array_merge($data, ['title' => 'Updated policy']))->assertSessionHasNoErrors();
        $this->delete('/admin/pages/'.$page->id)->assertRedirect();
        $this->get('/pages/privacy')->assertNotFound();
        $clean = CleanHtml::make('<svg><script>bad()</script></svg><p><a href="https://example.com">Safe</a></p>');
        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringContainsString('https://example.com', $clean);
    }

    public function test_contact_validation_approval_rejection_and_throttling(): void
    {
        $this->post('/contact', [])->assertSessionHasErrors(['name', 'email', 'message', 'consent']);
        $data = ['name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'App help',
            'message' => 'Please help me understand the app.', 'consent' => 1];
        $this->post('/contact', $data)->assertRedirect('/contact')->assertSessionHasNoErrors();
        $contact = ContactMessage::firstOrFail();
        $this->assertSame('pending', $contact->status);
        $this->post('/contact', $data)->assertStatus(429);
        $this->patch('/admin/contacts/'.$contact->id, ['status' => 'approved'])->assertRedirect('/admin/login');
        $this->actingAs($this->admin())->get('/admin/contacts')->assertOk()->assertSee('reader@example.com');
        $this->patch('/admin/contacts/'.$contact->id, ['status' => 'approved'])->assertRedirect();
        $this->assertSame('approved', $contact->refresh()->status);
        $this->patch('/admin/contacts/'.$contact->id, ['status' => 'rejected'])->assertRedirect();
        $this->assertSame('rejected', $contact->refresh()->status);
    }

    public function test_settings_validation_maintenance_and_history_deletion_are_scoped(): void
    {
        $admin = $this->admin();
        $owner = User::create(['name' => 'Owner', 'phone' => '9876543210', 'password' => '123456']);
        $other = User::create(['name' => 'Other', 'phone' => '9876543211', 'password' => '123456']);
        foreach ([$owner, $other] as $user) $user->locations()->create(['latitude' => 28.6, 'longitude' => 77.2, 'captured_at' => now()]);
        $this->actingAs($owner)->delete('/admin/accounts/'.$other->id.'/locations')->assertRedirect('/admin/login');
        $this->actingAs($admin)->get('/admin/settings')->assertOk()->assertSee('YouTube URL');
        $this->patch('/admin/settings', ['app_name' => 'IP Tracker', 'support_email' => 'bad', 'youtube_url' => 'javascript:alert(1)'])
            ->assertSessionHasErrors(['support_email', 'youtube_url']);
        $this->patch('/admin/settings', ['app_name' => 'IP Tracker', 'site_name' => 'My Tracker', 'site_url' => 'https://example.com',
            'support_email' => 'support@example.com', 'maintenance_mode' => 1])->assertSessionHasNoErrors();
        $this->get('/')->assertStatus(503)->assertSee('noindex, nofollow', false);
        $this->get('/admin')->assertOk();
        $this->delete('/admin/accounts/'.$owner->id.'/locations')->assertRedirect();
        $this->assertSame(0, $owner->locations()->count());
        $this->assertSame(1, $other->locations()->count());
    }

    public function test_legacy_admin_password_requires_a_six_digit_replacement(): void
    {
        $admin = User::create(['name' => 'Admin', 'phone' => '1234567890', 'password' => 'older-password', 'role' => 'admin']);
        $this->post('/admin/login', ['phone' => $admin->phone, 'password' => 'older-password'])->assertRedirect('/admin/profile');
        $this->get('/admin')->assertRedirect('/admin/profile');
        $this->patch('/admin/profile', ['name' => 'Admin', 'phone' => $admin->phone])->assertSessionHasErrors('password');
        $this->patch('/admin/profile', ['name' => 'Admin', 'phone' => $admin->phone,
            'current_password' => 'older-password', 'password' => '654321', 'password_confirmation' => '654321'])->assertSessionHasNoErrors();
        $this->get('/admin')->assertOk();
        $this->postJson('/api/login', ['phone' => '9876543210', 'password' => 'abcdef'])->assertUnprocessable();
    }

    public function test_deleted_history_cannot_be_reuploaded_from_an_old_offline_queue(): void
    {
        $owner = User::create(['name' => 'Owner', 'phone' => '9876543210', 'password' => '123456']);
        $token = $owner->createToken('test')->plainTextToken;
        $owner->forceFill(['locations_cleared_at' => now('UTC')])->save();
        $this->withToken($token)->postJson('/api/locations', ['latitude' => 28.6, 'longitude' => 77.2,
            'captured_at' => now('UTC')->subHour()->toAtomString(), 'full_address' => 'Previous address'])
            ->assertOk()->assertJsonPath('data.discarded', true);
        $this->assertDatabaseCount('user_locations', 0);
    }

    public function test_contact_throttling_separates_clients_behind_the_trusted_proxy(): void
    {
        $data = ['name' => 'Reader', 'email' => 'reader@example.com', 'subject' => 'App help',
            'message' => 'Please help me understand the app.', 'consent' => 1];
        $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
            ->withHeader('X-Forwarded-For', '198.51.100.10');
        $this->post('/contact', $data)->assertRedirect('/contact');
        $this->post('/contact', $data)->assertRedirect('/contact');
        $this->post('/contact', $data)->assertStatus(429);
        $this->withHeader('X-Forwarded-For', '198.51.100.11')
            ->post('/contact', $data)->assertRedirect('/contact');
    }

    public function test_phone_login_limit_survives_a_change_of_client_ip(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '127.0.0.1'])
                ->withHeader('X-Forwarded-For', '198.51.100.'.($attempt + 1))
                ->postJson('/api/login', ['phone' => '0000000000', 'password' => '123456'])->assertUnauthorized();
        }
        $this->withHeader('X-Forwarded-For', '198.51.100.99')
            ->postJson('/api/login', ['phone' => '0000000000', 'password' => '123456'])->assertStatus(429);
    }
}
