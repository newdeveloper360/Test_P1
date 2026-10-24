# Responsive website, admin and app advice

- Bootstrap 5.3.8 CSS and bundle are served locally from public/vendor/bootstrap. No CDN is needed for Bootstrap UI.
- Admin has a responsive offcanvas sidebar, header/footer, Bootstrap pagination, cards, tables and advice review modals.
- Search/filter accounts by name, phone, role and active/inactive/blocked state; blogs by title/status; pages by title; locations by address/city/date; advice by text/role/status. Existing contact status filtering remains.
- PATCH /admin/accounts/{user}/active accepts is_active=0 or 1. Deactivation revokes all Sanctum tokens; login and protected API middleware also check the database state. Activation permits a fresh login. Existing blocked status remains separate.
- POST /api/advice requires an authenticated active user/member, writable token, subject 3–120 characters and message 10–3000 characters. Limit: five attempts per hour/account. Client cannot supply owner/status/admin note.
- /admin/advice provides new/reviewed/planned/resolved statuses and an internal admin note. Suggestions are private, not published on the website.
- APK: Profile → Share app advice. Network failure retains typed input for retry.
- show_password is nullable encrypted text, hidden from JSON and excluded from mass assignment. Normal password remains hashed. A newly set user/member password updates both through the model; admin passwords are not copied. Existing hashes cannot be reversed, so existing accounts remain null until the next password update. Keep APP_KEY stable and protected; it decrypts this column.
- Sitemap and robots.txt remain public SEO endpoints; admin footer links to both. Admin pages stay noindex and disallowed in robots.txt.

Deploy normally; render-start.sh runs migrations automatically. Local migration: php artisan migrate --force. Verification: php -d short_open_tag=1 artisan test; Android: gradlew.bat :app:assembleDebug --offline.
