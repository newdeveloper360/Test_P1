@extends('admin.layout')
@section('title', $account->name.' · Locations')
@section('actions')<a class="btn" href="{{ route('admin.accounts.show',$account) }}">← Account</a>@endsection
@section('content')
<form method="get" class="card filters mb-4" data-local-dates>
<div class="field grow"><label for="q">Address / city / street</label><input class="form-control" id="q" name="q" value="{{ request('q') }}" maxlength="100"></div>
<div class="field"><label for="from">From date & time</label><input class="form-control" type="datetime-local" id="from" name="from" value="{{ strlen(request('from',''))===10 ? request('from').'T00:00' : request('from') }}">@error('from')<small class="text-danger">{{ $message }}</small>@enderror</div>
<div class="field"><label for="to">To date & time</label><input class="form-control" type="datetime-local" id="to" name="to" value="{{ strlen(request('to',''))===10 ? request('to').'T23:59' : request('to') }}">@error('to')<small class="text-danger">{{ $message }}</small>@enderror</div>
<input type="hidden" name="timezone" value="{{ request('timezone','') }}"><small data-zone-hint></small>
<button class="btn primary">Apply</button><a class="btn" href="{{ url()->current() }}">Reset</a></form>
<div class="card table-wrap table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Address</th><th>City / District</th><th>State / PIN</th><th>Captured</th><th>Stay time</th><th>Coordinates</th></tr></thead><tbody>
@forelse($locations as $location)
<tr><td class="location-address"><strong>{{ $location->place ?: 'Location' }}</strong><br><span class="muted">{{ $location->full_address ?: $location->street ?: 'Address unavailable' }}</span></td><td>{{ $location->city ?: '—' }}<br><span class="muted">{{ $location->district ?: '—' }}</span></td><td>{{ $location->state ?: '—' }}<br><span class="muted">{{ $location->postal_code ?: '—' }}</span></td><td>{{ $location->captured_at?->copy()->timezone(request('timezone') ?: config('app.timezone'))->format('d M Y') }}<br><span class="muted">{{ $location->captured_at?->copy()->timezone(request('timezone') ?: config('app.timezone'))->format('g:i:s A') }}</span></td><td>{{ $location->stay_seconds===null?'Current':\Carbon\CarbonInterval::seconds($location->stay_seconds)->cascade()->forHumans() }}</td><td><a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener">{{ $location->latitude }}, {{ $location->longitude }} ↗</a><br><span class="muted">{{ $location->device_name ?: 'Device unknown' }}</span></td></tr>
@empty<tr><td colspan="6" class="empty">No location history for this account.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $locations->links() }}</div>
@endsection

