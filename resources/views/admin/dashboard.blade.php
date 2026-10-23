@extends('admin.layout')
@section('title', 'Overview')
@section('actions')<a class="btn primary" href="{{ route('admin.accounts.create') }}">+ Add account</a>@endsection
@section('content')
<div class="grid stats">
    <div class="card stat"><div class="label">Users</div><div class="number">{{ $totalUsers }}</div><div class="hint">Accounts with invite codes</div></div>
    <div class="card stat"><div class="label">Members</div><div class="number">{{ $totalMembers }}</div><div class="hint">Connected to users</div></div>
    <div class="card stat"><div class="label">Blocked</div><div class="number">{{ $blocked }}</div><div class="hint">Cannot access the app</div></div>
    <div class="card stat"><div class="label">Location records</div><div class="number">{{ $locations }}</div><div class="hint">Saved by the app</div></div>
</div>
<div class="section-heading"><h2>Recent accounts</h2><a class="btn small" href="{{ route('admin.accounts.index') }}">View all</a></div>
<div class="card table-wrap"><table class="table"><thead><tr><th>Account</th><th>Role</th><th>Members</th><th>Status</th><th>Joined</th><th></th></tr></thead><tbody>
@forelse($recentAccounts as $account)
<tr><td><span class="avatar">{{ strtoupper(substr($account->name,0,1)) }}</span><strong>{{ $account->name }}</strong><br><span class="muted">{{ $account->phone }}</span></td><td><span class="badge {{ $account->role }}">{{ ucfirst($account->role) }}</span></td><td>{{ $account->members_count }}</td><td><span class="badge {{ $account->is_blocked ? 'blocked' : 'active' }}">{{ $account->is_blocked ? 'Blocked' : 'Active' }}</span></td><td>{{ $account->created_at?->format('d M Y') }}</td><td><a href="{{ route('admin.accounts.show',$account) }}">View →</a></td></tr>
@empty<tr><td colspan="6" class="empty">No accounts yet.</td></tr>@endforelse
</tbody></table></div>
<div class="section-heading"><h2>Latest locations</h2></div>
<div class="card table-wrap"><table class="table"><thead><tr><th>Account</th><th>Place</th><th>City</th><th>Captured</th><th></th></tr></thead><tbody>
@forelse($recentLocations as $location)
<tr><td><strong>{{ $location->user_name }}</strong><br><span class="muted">{{ $location->user_phone }}</span></td><td class="location-address">{{ $location->place ?: $location->full_address ?: 'Coordinates available' }}</td><td>{{ $location->city ?: '—' }}</td><td>{{ \Carbon\Carbon::parse($location->captured_at)->format('d M Y, g:i A') }}</td><td><a href="{{ route('admin.accounts.locations',$location->user_id) }}">History →</a></td></tr>
@empty<tr><td colspan="5" class="empty">No locations saved yet.</td></tr>@endforelse
</tbody></table></div>
@endsection
