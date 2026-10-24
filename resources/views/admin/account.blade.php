@extends('admin.layout')
@section('title', $account->name)
@section('actions')<a class="btn" href="{{ route('admin.accounts.index') }}">← Accounts</a><a class="btn primary" href="{{ route('admin.accounts.edit',$account) }}">Edit account</a>@endsection
@section('content')
<div class="grid stats">
    <div class="card stat"><div class="label">Role</div><div class="number" style="font-size:24px">{{ ucfirst($account->role) }}</div></div>
    <div class="card stat"><div class="label">Status</div><div class="number" style="font-size:24px">{{ $account->is_blocked?'Blocked':($account->is_active?'Active':'Inactive') }}</div></div>
    <div class="card stat"><div class="label">{{ $account->role==='user'?'Members':'Connected to' }}</div><div class="number" style="font-size:24px">{{ $account->role==='user'?$account->members_count:($account->invitedBy?->name ?: '—') }}</div></div>
    <div class="card stat"><div class="label">Location records</div><div class="number">{{ $account->locations_count }}</div></div>
</div>
<div class="grid two" style="margin-top:18px">
    <div class="card"><h2 style="margin-top:0">Account details</h2><p><strong>Phone:</strong> {{ $account->phone }}</p><p><strong>Joined:</strong> {{ $account->created_at?->format('d M Y, g:i A') }}</p>@if($account->role==='user')<p><strong>Invite code:</strong> {{ $account->invite_code ?: '—' }}</p>@else<p><strong>Owner:</strong> {{ $account->invitedBy?->name ?: '—' }}</p>@endif
        <div class="actions" style="margin-top:20px"><form method="post" action="{{ route('admin.accounts.active',$account) }}" data-confirm="Change account status? Deactivation signs out all app sessions.">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $account->is_active?0:1 }}"><button class="btn {{ $account->is_active?'danger':'primary' }}">{{ $account->is_active?'Deactivate account':'Activate account' }}</button></form><form method="post" action="{{ route('admin.accounts.block',$account) }}">@csrf @method('PATCH')<button class="btn {{ $account->is_blocked?'':'danger' }}" type="submit">{{ $account->is_blocked?'Unblock account':'Block account' }}</button></form><form method="post" action="{{ route('admin.accounts.destroy',$account) }}" data-confirm="Permanently delete this account and its location history?">@csrf @method('DELETE')<button class="btn danger" type="submit">Delete account</button></form></div>
    </div>
    <div class="card"><h2 style="margin-top:0">Recent sign-ins</h2>@forelse($loginEvents as $event)<p style="border-bottom:1px solid #edf1fa;padding-bottom:9px"><strong>{{ \Carbon\Carbon::parse($event->logged_in_at)->format('d M Y, g:i A') }}</strong><br><span class="muted">{{ $event->ip_address ?: 'Unknown IP' }} · {{ \Illuminate\Support\Str::limit($event->device ?: 'Unknown device',45) }}</span></p>@empty<p class="muted">No sign-ins recorded.</p>@endforelse</div>
</div>
@if($account->role==='user')
<div class="section-heading"><h2>Connected members</h2></div>
<div class="card table-wrap table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Name</th><th>Phone</th><th>Status</th><th></th></tr></thead><tbody>@forelse($members as $member)<tr><td><a href="{{ route('admin.accounts.show',$member) }}">{{ $member->name }}</a></td><td>{{ $member->phone }}</td><td><span class="badge {{ $member->is_blocked?'blocked':($member->is_active?'active':'inactive') }}">{{ $member->is_blocked?'Blocked':($member->is_active?'Active':'Inactive') }}</span></td><td><a href="{{ route('admin.accounts.locations',$member) }}">Locations →</a></td></tr>@empty<tr><td colspan="4" class="empty">No members connected yet.</td></tr>@endforelse</tbody></table></div>
@endif
<div class="section-heading"><h2>Recent locations</h2><a class="btn small" href="{{ route('admin.accounts.locations',$account) }}">Full history</a></div>
<div class="card table-wrap table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Place</th><th>City / District</th><th>Captured</th><th>Stay</th><th></th></tr></thead><tbody>@forelse($recentLocations as $location)<tr><td class="location-address">{{ $location->full_address ?: $location->place ?: 'Location recorded' }}</td><td>{{ $location->city ?: '—' }} / {{ $location->district ?: '—' }}</td><td>{{ $location->captured_at?->format('d M Y, g:i A') }}</td><td>{{ $location->stay_seconds===null?'Current':\Carbon\CarbonInterval::seconds($location->stay_seconds)->cascade()->forHumans() }}</td><td><a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" rel="noopener">Map ↗</a></td></tr>@empty<tr><td colspan="5" class="empty">No locations saved yet.</td></tr>@endforelse</tbody></table></div>
@endsection

