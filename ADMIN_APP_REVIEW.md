# Admin UI and Android location update

## Admin
- Sidebar: Dashboard, Users, Sliders, Pages, Blogs, Contact Us, App Advice, Settings.
- Real account/content counts on the dashboard; links open the relevant lists/filters.
- Header: working user search, profile avatar dropdown with My Profile and POST/CSRF Logout.
- Compact tables, initials avatars, colored role/status pills, account actions in a dialog.
- Shared confirmation modal for deleting accounts/history/sliders/blogs/pages and changing account status.
- Dismissible success/error alerts and existing per-field validation messages.
- Sitemap/robots links inside Settings; footer contains Settings, public website and profile links.
- Assets live in public/panel-ui, deliberately avoiding a public/admin directory that would shadow the Laravel /admin route.

## Android
- Location requests remain every two seconds. History accuracy/movement confirmation gates are unchanged.
- Recent accurate GPS fixes are preferred over substantially less accurate network fixes.
- Incomplete address fields retry after a minute even when stationary.
- Late geocoder callbacks cannot overwrite a timed-out lookup; address snapshots moved over 50m away are discarded and refreshed.
- Provider results farther than max(150m, 3 × reported accuracy) from the requested point are ignored; no merging of neighboring geocoder results.
- Consistency checks prevent mixing fallback fields from a different city/state/country.
- Empty area values can use premises/locality; a missing full address is built from available structured fields. Country-only responses are not treated as saved place addresses.
- A moderate-accuracy display remains marked approximate; very poor (>100m) readings wait for a better fix before resolving the displayed address. History still requires reported accuracy <=35m.
- Flaticon animated location pin is bundled for offline display and filtered to the app's blue theme, with Profile attribution and packaged third-party notices. Respects disabled system animations and stops when detached/hidden.
- GPS and geocoder coverage cannot guarantee a building-level address or a PIN code for every reading. No synthetic address fields are created.

Verification: complete Laravel test suite, emulator location/address regression tests and animation decode/theme check; desktop/mobile browser review of sidebar, profile dropdown, account actions, delete cancellation, success alert and Settings SEO links.
