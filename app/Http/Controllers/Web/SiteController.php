<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\ContactMessage;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SiteController extends Controller
{
    public const FAQ = [
        ['How does IP Tracker find my location?', 'Your phone uses the Android location services you allow. GPS, network signals and device settings affect the available reading. Addresses are looked up from those coordinates.'],
        ['Is my location always exact?', 'No. Buildings, signal conditions and approximate-location permission can affect accuracy. The app shows the reported accuracy and waits for suitable readings before recording changes.'],
        ['How does stay time work?', 'Stay time starts at a recorded location change and continues until another change is recorded. It is an estimate based on the readings available to the phone.'],
        ['What happens without internet?', 'The app stores pending location changes locally and retries syncing when connected. Address lookup may need a network connection before a record can be uploaded.'],
        ['Who can see member locations?', 'The user whose invite code a member joins with can view that member’s records. Administrators can manage accounts and records. Share an invite code only with people you trust.'],
        ['Does tracking work when the app screen is closed?', 'A foreground service can continue tracking with an Android notification and suitable permissions. Force stop, disabled permissions, battery restrictions or device settings can interrupt it.'],
        ['How do I stop location tracking?', 'Sign out of the app or manage location permission in Android settings. Allow precise location for the best available accuracy when using the app.'],
    ];

    private function site(string $view, array $data = [])
    {
        $setting = Setting::current();
        $name = $setting->site_name ?: $setting->app_name;
        $base = rtrim($setting->site_url ?: url('/'), '/');
        $defaults = [
            'setting' => $setting, 'siteName' => $name,
            'footerPages' => Page::orderBy('title')->get(['title', 'slug']),
            'seoTitle' => $setting->seo_title ?: $name.' — Your places, clearly connected',
            'seoDescription' => $setting->seo_description ?: 'Your phone location, place history and connected members in one Android app.',
            'seoKeywords' => $setting->seo_keywords,
            'canonical' => $base.(request()->path() === '/' ? '/' : '/'.request()->path()),
            'ogImage' => $setting->logo_url ? url($setting->logo_url) : asset('assets/frontend/images/social-card.png'),
            'faq' => self::FAQ,
        ];
        if ($setting->maintenance_mode) {
            return response()->view('site.maintenance', array_merge($defaults, ['noindex' => true]), 503)
                ->header('Retry-After', '3600');
        }

        return view($view, array_merge($defaults, $data));
    }

    public function home()
    {
        return $this->site('site.home', ['blogs' => Blog::where('is_published', true)
            ->select(['id', 'title', 'slug', 'short_description', 'created_at'])->latest()->limit(3)->get()]);
    }

    public function about()
    {
        return $this->site('site.about', ['pageTitle' => 'About', 'seoDescription' => 'Learn how IP Tracker brings Android phone locations, place history and connected members into one clear view.']);
    }

    public function features()
    {
        return $this->site('site.features', ['pageTitle' => 'Features', 'seoDescription' => 'Explore IP Tracker features: current location details, address history, stay duration, member invite codes, offline sync and Google Maps.']);
    }

    public function faq()
    {
        return $this->site('site.faq', ['pageTitle' => 'Frequently asked questions', 'seoDescription' => 'Find answers about IP Tracker location accuracy, Android permissions, background tracking, offline sync, member sharing and stay time.']);
    }

    public function contact()
    {
        return $this->site('site.contact', ['pageTitle' => 'Contact us', 'seoDescription' => 'Contact the IP Tracker support team for help with your account, Android app or connected members.']);
    }

    public function sendContact(Request $request)
    {
        abort_if(Setting::current()->maintenance_mode, 503, 'Please try again after maintenance.');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['nullable', 'string', 'regex:/^\+?[0-9 ()-]{7,20}$/'],
            'subject' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
            'consent' => ['accepted'],
            'website' => ['nullable', 'max:0'],
        ]);
        unset($data['consent'], $data['website']);
        ContactMessage::create($data);

        return redirect()->route('site.contact')->with('success', 'Thank you. Your message has been sent for review.');
    }

    public function blogs()
    {
        return $this->site('site.blogs', ['pageTitle' => 'The journal', 'seoDescription' => 'Read IP Tracker app updates, location guides and helpful articles for a more connected day.',
            'blogs' => Blog::where('is_published', true)->select(['id', 'title', 'slug', 'short_description', 'created_at'])->latest()->paginate(9)]);
    }

    public function blog(Blog $blog)
    {
        abort_unless($blog->is_published, 404);

        return $this->site('site.blog', ['blog' => $blog, 'pageTitle' => $blog->title,
            'seoTitle' => $blog->meta_title ?: $blog->title,
            'seoDescription' => $blog->meta_description ?: $blog->short_description,
            'seoKeywords' => $blog->meta_keywords, 'ogImage' => route('site.blog.image', $blog), 'isArticle' => true]);
    }

    public function page(Page $page)
    {
        return $this->site('site.page', ['page' => $page, 'pageTitle' => $page->title,
            'seoDescription' => mb_substr(trim(strip_tags($page->content)), 0, 160)]);
    }

    public function image(Blog $blog)
    {
        abort_unless($blog->is_published || Auth::guard('web')->user()?->role === 'admin', 404);
        if ($blog->featured_image_data) {
            $data = base64_decode($blog->featured_image_data, true);
            abort_unless($data !== false && in_array($blog->featured_image_mime, ['image/jpeg', 'image/png', 'image/webp'], true), 404);

            return response($data)->header('Content-Type', $blog->featured_image_mime)
                ->header('X-Content-Type-Options', 'nosniff')->header('Cache-Control', 'public, max-age=3600')
                ->setEtag(hash('sha256', $data));
        }
        abort_unless($blog->featured_image && Storage::disk('public')->exists($blog->featured_image), 404);

        return Storage::disk('public')->response($blog->featured_image);
    }

    public function download()
    {
        $target = Setting::current()->android_update_url;
        if ($target && ! in_array(rtrim($target, '/'), [rtrim(route('site.download'), '/'), rtrim(url('/download'), '/')], true)) {
            abort_unless(filter_var($target, FILTER_VALIDATE_URL) && in_array(parse_url($target, PHP_URL_SCHEME), ['http', 'https'], true), 404);

            return redirect()->away($target);
        }
        $path = public_path('downloads/ip-tracker.apk');
        abort_unless(is_file($path), 404, 'The Android download is not available yet.');

        return response()->download($path, 'IP-Tracker.apk', ['Content-Type' => 'application/vnd.android.package-archive',
            'X-Content-Type-Options' => 'nosniff']);
    }

    public function sitemap()
    {
        $setting = Setting::current();
        $base = rtrim($setting->site_url ?: url('/'), '/');
        $urls = collect(['/', '/about', '/features', '/faq', '/contact', '/blog'])
            ->map(fn ($path) => ['loc' => $base.$path, 'updated' => null]);
        foreach (Blog::where('is_published', true)->get(['slug', 'updated_at']) as $blog) {
            $urls->push(['loc' => $base.'/blog/'.$blog->slug, 'updated' => $blog->updated_at->toAtomString()]);
        }
        foreach (Page::all(['slug', 'updated_at']) as $page) {
            $urls->push(['loc' => $base.'/pages/'.$page->slug, 'updated' => $page->updated_at->toAtomString()]);
        }

        return response()->view('site.sitemap', compact('urls'))->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots()
    {
        $base = rtrim(Setting::current()->site_url ?: url('/'), '/');

        return response("User-agent: *\nAllow: /\nDisallow: /admin\nDisallow: /api\nSitemap: {$base}/sitemap.xml\n")
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
