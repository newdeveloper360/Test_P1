@extends('admin.layout')
@section('title', 'Accounts')
@section('actions')<a class="btn primary" href="{{ route('admin.accounts.create') }}">+ Add account</a>@endsection
@section('content')
<form method="get" action="{{ route('admin.accounts.index') }}" class="card filters">
    <div class="field grow"><label for="q">Search</label><input class="form-control" id="q" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name or phone"></div>
    <div class="field"><label for="role">Role</label><select class="form-select" id="role" name="role"><option value="">All roles</option><option value="user" @selected(($filters['role'] ?? '')==='user')>Users</option><option value="member" @selected(($filters['role'] ?? '')==='member')>Members</option></select></div>
    <div class="field"><label for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All statuses</option><option value="active" @selected(($filters['status'] ?? '')==='active')>Active</option><option value="inactive" @selected(($filters['status'] ?? '')==='inactive')>Inactive</option><option value="blocked" @selected(($filters['status'] ?? '')==='blocked')>Blocked</option></select></div>
    <button class="btn primary" type="submit">Apply filters</button><a class="btn" href="{{ route('admin.accounts.index') }}">Clear</a>
</form>
<div class="section-heading"><h2>{{ $accounts->total() }} account{{ $accounts->total()===1?'':'s' }}</h2></div>
<div class="card table-wrap table-responsive"><table class="table table-hover align-middle"><thead><tr><th>Account</th><th>Role</th><th>Connection</th><th>Location</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($accounts as $account)
<tr>
    <td><span class="avatar">{{ strtoupper(substr($account->name,0,1)) }}</span><a href="{{ route('admin.accounts.show',$account) }}">{{ $account->name }}</a><br><span class="muted">{{ $account->phone }}</span></td>
    <td><span class="badge {{ $account->role }}">{{ ucfirst($account->role) }}</span></td>
    <td>{{ $account->role==='user' ? $account->members_count.' members' : ($account->invitedBy?->name ?: 'No owner') }}</td>
    <td>{{ $account->locations_count }} records<br><span class="muted">{{ $account->locations_max_captured_at ? \Carbon\Carbon::parse($account->locations_max_captured_at)->diffForHumans() : 'No update' }}</span></td>
    <td><span class="badge {{ $account->is_blocked?'blocked':($account->is_active?'active':'blocked') }}">{{ $account->is_blocked?'Blocked':($account->is_active?'Active':'Inactive') }}</span></td>
    <td class="actions"><form method="post" action="{{ route('admin.accounts.active',$account) }}" onsubmit="return confirm('Change this account status? Deactivation signs out all app sessions.');">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $account->is_active ? 0 : 1 }}"><button class="btn small {{ $account->is_active?'danger':'primary' }}">{{ $account->is_active?'Deactivate':'Activate' }}</button></form><a class="btn small" href="{{ route('admin.accounts.show',$account) }}">View</a><a class="btn small" href="{{ route('admin.accounts.edit',$account) }}">Edit</a><form method="post" action="{{ route('admin.accounts.block',$account) }}">@csrf @method('PATCH')<button class="btn small {{ $account->is_blocked?'':'danger' }}" type="submit">{{ $account->is_blocked?'Unblock':'Block' }}</button></form><form method="post" action="{{ route('admin.accounts.locations.destroy',$account) }}" onsubmit="return confirm('Permanently delete ALL location history for this account? This cannot be undone.');">@csrf @method('DELETE')<button class="btn small danger" type="submit" @disabled(!$account->locations_count)>Delete all history</button></form></td>
</tr>
@empty<tr><td colspan="6" class="empty">No accounts match these filters.</td></tr>@endforelse
</tbody></table></div>
<div class="pagination">{{ $accounts->links() }}</div>
@endsection

