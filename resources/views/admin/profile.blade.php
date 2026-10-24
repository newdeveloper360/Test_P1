@extends('admin.layout')
@section('title', 'My profile')
@section('content')
<div class="card" style="max-width:700px"><h2 style="margin-top:0">Administrator details</h2><p class="muted">Change your contact details or six-digit password.</p>
    <form method="post" action="{{ route('admin.profile.update') }}">@csrf @method('PATCH')
        <div class="form-grid"><div class="field"><label for="name">Name</label><input class="form-control" id="name" name="name" value="{{ old('name',$admin->name) }}" maxlength="100" required></div><div class="field"><label for="phone">Phone</label><input class="form-control" id="phone" name="phone" inputmode="numeric" maxlength="10" value="{{ old('phone',$admin->phone) }}" required></div></div>
        <div class="section-heading"><h2>Change password</h2></div>
        <div class="form-grid"><div class="field"><label for="current_password">Current password</label><input class="form-control" id="current_password" type="password" name="current_password" autocomplete="current-password"></div><div class="field"><label for="password">New six-digit password</label><input class="form-control" id="password" type="password" name="password" inputmode="numeric" maxlength="6" autocomplete="new-password"></div><div class="field"><label for="password_confirmation">Confirm new password</label><input class="form-control" id="password_confirmation" type="password" name="password_confirmation" inputmode="numeric" maxlength="6" autocomplete="new-password"></div></div>
        <div class="form-actions"><button class="btn primary" type="submit">Save profile</button></div>
    </form>
</div>
@endsection

