<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">@switch($name ?? 'dashboard')
@case('users')<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M17 5a3 3 0 0 1 0 6m4 10v-3a6 6 0 0 0-3-5"/>@break
@case('sliders')<rect x="3" y="4" width="18" height="16" rx="3"/><circle cx="8" cy="9" r="1.5"/><path d="m3 17 5-5 4 4 4-6 5 7"/>@break
@case('pages')<path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9zM14 3v6h6M8 13h8M8 17h5"/>@break
@case('blogs')<path d="m16 3 5 5-11 11-7 2 2-7zM14 5l5 5"/>@break
@case('contacts')<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/>@break
@case('advice')<path d="M5 18h-2V4h18v14H10l-5 4zM7 8h10M7 12h6"/>@break
@case('settings')<path d="m10 3-1 3-3 1-3 3 2 2-1 3 3 3 3-1 2 2 3-1 1-3 3-1v-4l-3-1-1-3z"/><circle cx="12" cy="12" r="3"/>@break
@case('profile')<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
@case('logout')<path d="M9 4H4v16h5m5-4 4-4-4-4M8 12h13"/>@break
@case('search')<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('warning')<path d="m12 3 10 18H2zM12 9v5m0 3h.01"/>@break
@case('pin')<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="3"/>@break
@case('arrow')<path d="M5 12h14m-5-5 5 5-5 5"/>@break
@default<rect x="3" y="3" width="7" height="7" rx="2"/><rect x="14" y="3" width="7" height="7" rx="2"/><rect x="3" y="14" width="7" height="7" rx="2"/><rect x="14" y="14" width="7" height="7" rx="2"/>
@endswitch</svg>
