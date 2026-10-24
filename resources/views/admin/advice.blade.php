@extends('admin.layout')
@section('title','App advice')
@section('content')
<p class="text-secondary mb-4">Ideas and suggestions sent by users and members from the Android app.</p>
<form method="get" class="card filters mb-4">
<div class="field grow"><label for="q">Search advice</label><input class="form-control" id="q" name="q" value="{{ request('q') }}" placeholder="Subject, message, name or phone"></div>
<div class="field"><label for="status">Status</label><select class="form-select" id="status" name="status"><option value="">All statuses</option>@foreach(['new','reviewed','planned','resolved'] as $status)<option @selected(request('status')===$status) value="{{ $status }}">{{ ucfirst($status) }}</option>@endforeach</select></div>
<div class="field"><label for="role">Role</label><select class="form-select" id="role" name="role"><option value="">All roles</option>@foreach(['user','member'] as $role)<option @selected(request('role')===$role) value="{{ $role }}">{{ ucfirst($role) }}</option>@endforeach</select></div>
<button class="btn primary">Apply</button><a class="btn" href="{{ route('admin.advice.index') }}">Reset</a></form>
<div class="d-flex justify-content-between mb-3"><strong>{{ $advice->total() }} suggestions</strong><span class="muted">Newest first</span></div>
<div class="grid">@forelse($advice as $item)<article class="card">
<div class="d-flex justify-content-between gap-3 flex-wrap"><div><span class="badge {{ $item->status==='resolved'?'active':'' }}">{{ ucfirst($item->status) }}</span><h2 class="h5 mt-3">{{ $item->subject }}</h2><p class="small text-secondary">{{ $item->user?->name ?? 'Deleted account' }} · {{ $item->user?->phone }} · {{ ucfirst($item->user?->role ?? '') }}</p></div><small class="text-secondary">{{ $item->created_at->format('d M Y, h:i A') }}</small></div>
<p class="advice-message">{{ $item->message }}</p>
<button class="btn small" data-bs-toggle="modal" data-bs-target="#advice-{{ $item->id }}">Review advice</button>
<div class="modal fade" id="advice-{{ $item->id }}" tabindex="-1" aria-labelledby="advice-title-{{ $item->id }}" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form method="post" action="{{ route('admin.advice.update',$item) }}">@csrf @method('PATCH')<div class="modal-header"><h2 class="modal-title fs-5" id="advice-title-{{ $item->id }}">Review advice #{{ $item->id }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><div class="field"><label for="status-{{ $item->id }}">Status</label><select class="form-select" id="status-{{ $item->id }}" name="status">@foreach(['new','reviewed','planned','resolved'] as $status)<option @selected($item->status===$status)>{{ $status }}</option>@endforeach</select></div><div class="field mt-3"><label for="note-{{ $item->id }}">Internal admin note</label><textarea class="form-control" id="note-{{ $item->id }}" name="admin_note" maxlength="2000">{{ $item->admin_note }}</textarea></div></div><div class="modal-footer"><button class="btn" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn primary">Save review</button></div></form></div></div></div>
@if($item->admin_note)<p class="small text-secondary mt-3 mb-0">Admin note: {{ $item->admin_note }}</p>@endif
</article>@empty<div class="card empty">No advice matches these filters.</div>@endforelse</div>{{ $advice->links() }}
@endsection

