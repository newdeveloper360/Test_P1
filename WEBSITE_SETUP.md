# Website and admin

The existing Laravel app now serves the public website at `/`. Admin remains at `/admin/login`.

## Public pages

- `/`, `/about`, `/features`, `/faq`, `/contact`, `/blog`
- Published articles: `/blog/{slug}`
- Editable policy/content pages: `/pages/{slug}`
- `/download` serves `public/downloads/ip-tracker.apk`, or redirects to the external APK URL in settings.
- `/sitemap.xml` includes public pages and published articles. `/robots.txt` excludes admin and API paths.

Use **Website & app settings** for the site name/URL, phone, WhatsApp (with country code), support email, social/YouTube URLs and default SEO title/description/keywords. Empty contact/social values do not render. Set the final website URL after assigning a domain. Production-generated URLs use HTTPS.

`php artisan migrate --force` adds the new schema. `php artisan db:seed --class=WebsiteSeeder --force` creates default SEO values and starter Privacy Policy/Terms pages without overwriting existing records. Review and edit those pages for your actual service before publishing them as your final policies. Render startup runs these commands automatically.

## Admin features

- **Blog posts**: title, unique editable slug, short description, featured image, content, SEO fields, draft/published state, edit and delete.
- **Website pages**: title, unique slug and content, with create/edit/delete.
- **Contact requests**: private submissions with pending/approved/rejected status. Approval does not publish a visitor's personal information.
- **Accounts → Delete all history**: permanently deletes only that account's locations. Older queued offline records captured before the reset are discarded by the API.

Featured blog images accept actual JPG/PNG/WebP images up to 2 MB and 6000×6000 pixels. Image data is stored in the database so it survives Render web-service restarts. Other existing branding/slider uploads still use the configured public filesystem.

Content is sanitized on the server. Scripts, event handlers, embedded frames and unsafe links are removed. Forms use CSRF protection. Contact requests are limited to 2/minute and 5/hour per IP; admin routes to 120/minute. Production rate limits use the database cache even if the normal cache uses `array`.

## Editor

At the owner's request, admin uses CKEditor 4.22.1 from the official CKEditor CDN. This free build reached end of support and displays the vendor's security-update warning. It is limited to authenticated admins, and published HTML is sanitized. The supported CKEditor 4 LTS edition requires a commercial license. Do not disable the vendor's warning to disguise its support status.

Official notice: https://ckeditor.com/docs/ckeditor4/latest/support/licensing/extended-support-model.html

## Passwords and location

User/member passwords and new admin passwords require exactly six digits. Existing longer admin credentials can sign in to the profile page once, where a six-digit replacement is required before accessing the panel.

The Android app still requests location updates every two seconds. History rejects stale fixes (older than 45 seconds), invalid coordinates and readings with reported accuracy worse than 35 metres, including the first fix. Movement confirmation and address comparison remain in place. Address fields come from one reverse-geocoding result rather than mixing neighbouring addresses. GPS accuracy and address availability depend on the device and providers; an exact building cannot be guaranteed.

## Checks

Run `php artisan test` for the website/API suite. Run `gradlew.bat :app:connectedDebugAndroidTest --offline` from the Android project with a test emulator for PIN, address parsing, icons, queue storage and location-quality checks.

Rebuild and replace the bundled APK when releasing an app update. Set `android_version_code` and the download URL to match the intended release. Free Render Postgres has its own expiry and capacity limits; keep database backups outside the service.
