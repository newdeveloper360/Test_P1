@extends('admin.layout')
@section('title', $account ? 'Edit account' : 'Add account')
@section('actions')<a class="btn" href="{{ $account ? route('admin.accounts.show',$account) : route('admin.accounts.index') }}">← Back</a>@endsection
@section('content')
<div class="card" style="max-width:760px">
    <form method="post" action="{{ $account ? route('admin.accounts.update',$account) : route('admin.accounts.store') }}">
        @csrf @if($account) @method('PATCH') @endif
        <div class="form-grid">
            <div class="field"><label for="name">Full name</label><input id="name" name="name" maxlength="100" value="{{ old('name',$account?->name) }}" required>@error('name')<span class="error">{{ $message }}</span>@enderror</div>
            <div class="field"><label for="phone">Phone number</label><input id="phone" name="phone" inputmode="numeric" maxlength="10" value="{{ old('phone',$account?->phone) }}" required>@error('phone')<span class="error">{{ $message }}</span>@enderror</div>
            @unless($account)
            <div class="field"><label for="role">Role</label><select id="role" name="role" required><option value="user" @selected(old('role')==='user')>User</option><option value="member" @selected(old('role')==='member')>Member</option></select><small>Users receive a unique six-digit invite code.</small></div>
            <div class="field"><label for="owner">Member's user</label><select id="owner" name="invited_by_user_id"><option value="">Select a user for member accounts</option>@foreach($owners as $owner)<option value="{{ $owner->id }}" @selected(old('invited_by_user_id')==$owner->id)>{{ $owner->name }} · {{ $owner->phone }}</option>@endforeach</select>@error('invited_by_user_id')<span class="error">{{ $message }}</span>@enderror</div>
            @endunless
            <div class="field full"><label for="password">{{ $account ? 'New password (optional)' : 'Password' }}</label><input id="password" type="password" name="password" inputmode="numeric" maxlength="6" {{ $account?'':'required' }} autocomplete="new-password"><small>Exactly six digits.</small>@error('password')<span class="error">{{ $message }}</span>@enderror</div>
        </div>
        <div class="form-actions"><button class="btn primary" type="submit">{{ $account ? 'Save changes' : 'Create account' }}</button><a class="btn" href="{{ route('admin.accounts.index') }}">Cancel</a></div>
    </form>
</div>
@endsection
