<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name ?? 'pin')
@case('download')<path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('people')<circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6m3 10v-3a6 6 0 0 0-3-5"/>@break
@case('shield')<path d="m12 2 8 4v6c0 5-8 10-8 10S4 17 4 12V6zM8 12l3 3 5-6"/>@break
@case('sync')<path d="M20 7a9 9 0 0 0-15-2L2 8m0-5v5h5m-3 9a9 9 0 0 0 15 2l3-3m0 5v-5h-5"/>@break
@case('arrow')<path d="M4 12h16m-6-6 6 6-6 6"/>@break
@case('mail')<rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/>@break
@case('phone')<path d="m5 3 4 4-2 3a16 16 0 0 0 7 7l3-2 4 4c-2 6-8 3-13-2S0 5 5 3z"/>@break
@default<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="2.5"/>
@endswitch
</svg>
