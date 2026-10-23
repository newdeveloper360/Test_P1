@extends('admin.layout')
@section('title', 'Admin sign in')
@section('content')
<div class="login-wrap">
    <div class="card login-card">
        <div class="brand-mark" aria-hidden="true">⌖</div>
        <h1>Welcome back</h1>
        <p>Sign in to manage accounts, locations, sliders and app settings.</p>
        @if($errors->any())<div class="flash error" role="alert">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('admin.login.submit') }}">
            @csrf
            <div class="field"><label for="phone">Admin phone</label><input id="phone" name="phone" inputmode="numeric" autocomplete="username" maxlength="10" value="{{ old('phone') }}" required></div>
            <div class="field"><label for="password">Password</label><input id="password" type="password" name="password" autocomplete="current-password" required></div>
            <button class="btn primary" type="submit">Sign in to dashboard →</button>
        </form>
    </div>
</div>
@endsection
