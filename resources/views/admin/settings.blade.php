@extends('admin.layout')
@section('title', 'App settings')
@section('content')
<div class="grid two">
    <div class="card"><h2 style="margin-top:0">Branding and availability</h2><p class="muted">These values appear in the Android app when it connects to the API.</p>
        <form method="post" action="{{ route('admin.settings.update') }}">@csrf @method('PATCH')
            <div class="field"><label for="app_name">App name</label><input id="app_name" name="app_name" maxlength="60" value="{{ old('app_name',$setting->app_name) }}" required></div>
            <div class="field" style="margin-top:14px"><label for="banner_text">Welcome message</label><textarea id="banner_text" name="banner_text" maxlength="200">{{ old('banner_text',$setting->banner_text) }}</textarea></div>
            <div class="field" style="margin-top:14px"><label class="check"><input type="checkbox" name="maintenance_mode" value="1" @checked(old('maintenance_mode',$setting->maintenance_mode))> Maintenance mode</label><small>Users and members cannot sign in while enabled.</small></div>
            <div class="field" style="margin-top:14px"><label for="maintenance_message">Maintenance message</label><textarea id="maintenance_message" name="maintenance_message" maxlength="200">{{ old('maintenance_message',$setting->maintenance_message) }}</textarea></div>
            <div class="field" style="margin-top:20px"><label for="android_version_code">Latest Android version code</label><input id="android_version_code" name="android_version_code" type="number" min="1" max="2100000000" value="{{ old('android_version_code',$setting->android_version_code) }}" required><small>This APK uses version code 1. Enter a higher code after publishing a newer APK to show the update prompt.</small>@error('android_version_code')<small style="color:#b42318">{{ $message }}</small>@enderror</div>
            <div class="field" style="margin-top:14px"><label for="android_update_url">Android update link</label><input id="android_update_url" name="android_update_url" type="url" maxlength="2048" placeholder="https://example.com/app.apk" value="{{ old('android_update_url',$setting->android_update_url) }}"><small>Link to the latest APK or download page. Leave blank to tell users to contact the administrator.</small>@error('android_update_url')<small style="color:#b42318">{{ $message }}</small>@enderror</div>
            <div class="form-actions"><button class="btn primary" type="submit">Save settings</button></div>
        </form>
    </div>
    <div class="grid" style="align-content:start">
        <div class="card"><h2 style="margin-top:0">App logo</h2>@if($setting->logo_url)<img class="preview" src="{{ $setting->logo_url }}" alt="Current logo">@endif<form method="post" action="{{ route('admin.settings.image','logo') }}" enctype="multipart/form-data">@csrf<div class="field" style="margin-top:14px"><label for="logo">Upload logo</label><input id="logo" type="file" name="image" accept="image/jpeg,image/png,image/webp" required><small>JPG, PNG or WebP up to 2 MB.</small></div><div class="form-actions"><button class="btn" type="submit">Update logo</button></div></form></div>
        <div class="card"><h2 style="margin-top:0">Splash banner</h2>@if($setting->banner_image_url)<img class="preview" src="{{ $setting->banner_image_url }}" alt="Current banner">@endif<form method="post" action="{{ route('admin.settings.image','banner') }}" enctype="multipart/form-data">@csrf<div class="field" style="margin-top:14px"><label for="banner">Upload banner</label><input id="banner" type="file" name="image" accept="image/jpeg,image/png,image/webp" required><small>JPG, PNG or WebP up to 2 MB.</small></div><div class="form-actions"><button class="btn" type="submit">Update banner</button></div></form></div>
    </div>
</div>
@endsection
