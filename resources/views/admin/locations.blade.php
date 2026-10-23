@extends('admin.layout')
@section('title', $account->name.' · Locations')
@section('actions')<a class="btn" href="{{ route('admin.accounts.show',$account) }}">← Account</a>@endsection
@section('content')
<div class="card table-wrap"><table class="table"><thead><tr><th>Address</th><th>City / District</th><th>State / PIN</th><th>Captured</th><th>Stay time</th><th>Coordinates</th></tr></thead><tbody>
@forelse($locations as $location)
<tr><td class="location-address"><strong>{{ $location->place ?: 'Location' }}</strong><br><span class="muted">{{ $location->full_address ?: $location->street ?: 'Address unavailable' }}</span></td><td>{{ $location->city ?: '—' }}<br><span class="muted">{{ $location->district ?: '—' }}</span></td><td>{{ $location->state ?: '—' }}<br><span class="muted">{{ $location->postal_code ?: '—' }}</span></td><td>{{ $location->captured_at?->format('d M Y') }}<br><span class="muted">{{ $location->captured_at?->format('g:i:s A') }}</span></td><td>{{ $location->stay_seconds===null?'Current':\Carbon\CarbonInterval::seconds($location->stay_seconds)->cascade()->forHumans() }}</td><td><a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener">{{ $location->latitude }}, {{ $location->longitude }} ↗</a><br><span class="muted">{{ $location->device_name ?: 'Device unknown' }}</span></td></tr>
@empty<tr><td colspan="6" class="empty">No location history for this account.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $locations->links() }}</div>
@endsection
