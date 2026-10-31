<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0c1734">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') · IP Tracker</title>
    <link rel="stylesheet" href="{{ asset('assets/backend/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/backend/css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/backend/css/panel.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('assets/backend/css/roboto.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/backend/css/advisory-theme.css') }}?v=1">
</head>
<body>
@if(Auth::guard('web')->user()?->role==='admin')
<div class="shell"><aside class="sidebar offcanvas-lg offcanvas-start" id="adminSidebar" tabindex="-1" aria-labelledby="sidebarTitle"><div class="offcanvas-header d-lg-none"><strong id="sidebarTitle">Navigation</strong><button class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#adminSidebar" aria-label="Close navigation"></button></div><div class="offcanvas-body">
<a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">@include('admin.icon',['name'=>'pin'])</span><span>IP Tracker<small class="brand-caption">ADMIN WORKSPACE</small></span></a>
<nav class="nav" aria-label="Admin navigation">@foreach([['dashboard','Dashboard','admin.dashboard','admin.dashboard'],['users','Users','admin.accounts.index','admin.accounts.*'],['sliders','Sliders','admin.sliders','admin.sliders*'],['pages','Pages','admin.pages.index','admin.pages.*'],['blogs','Blogs','admin.blogs.index','admin.blogs.*'],['contacts','Contact Us','admin.contacts.index','admin.contacts.*'],['advice','App Advice','admin.advice.index','admin.advice.*'],['settings','Settings','admin.settings','admin.settings*']] as [$icon,$label,$route,$pattern])<a class="{{ request()->routeIs($pattern)?'active':'' }}" @if(request()->routeIs($pattern)) aria-current="page" @endif href="{{ route($route) }}">@include('admin.icon',['name'=>$icon])<span>{{ $label }}</span></a>@endforeach</nav>
<div class="sidebar-bottom"><p class="side-note mb-0">Your accounts. Your app.<br>Everything, in one workspace.</p></div></div></aside>
<main class="main"><header class="utility-header"><div class="utility-left"><button class="mobile-menu-button d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-label="Open sidebar">☰</button><span class="utility-title">Administration</span></div><form class="utility-search" method="get" action="{{ route('admin.accounts.index') }}" role="search">@include('admin.icon',['name'=>'search'])<input name="q" maxlength="100" aria-label="Search users" placeholder="Search users by name or phone…"><button class="visually-hidden" type="submit">Search</button></form><div class="dropdown"><button class="profile-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Open profile menu"><span class="avatar">{{ mb_strtoupper(mb_substr(Auth::guard('web')->user()->name,0,1)) }}</span><span class="profile-copy"><strong>{{ Auth::guard('web')->user()->name }}</strong><small>Administrator</small></span><span class="muted small">⌄</span></button><div class="dropdown-menu dropdown-menu-end profile-dropdown"><a class="dropdown-item" href="{{ route('admin.profile') }}">@include('admin.icon',['name'=>'profile']) My Profile</a><div class="dropdown-divider"></div><form action="{{ route('admin.logout') }}" method="post">@csrf<button class="dropdown-item text-danger" type="submit">@include('admin.icon',['name'=>'logout']) Logout</button></form></div></div></header>
<div class="topbar"><div><div class="eyebrow">WORKSPACE / @yield('title','Dashboard')</div><h1 class="page-title">@yield('title','Dashboard')</h1></div><div class="top-action">@yield('actions')</div></div>
<div class="main-content">
@if(session('success'))<div class="flash success alert alert-dismissible" role="status">@include('admin.icon',['name'=>'check'])<span>{{ session('success') }}</span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss success message"></button></div>@endif
@if(session('error') || $errors->any())<div class="flash error alert alert-dismissible" role="alert">@include('admin.icon',['name'=>'warning'])<span>{{ session('error') ?: $errors->first() }}</span><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Dismiss error message"></button></div>@endif
@yield('content')
</div><footer class="admin-footer"><span>© {{ date('Y') }} IP Tracker · Admin workspace</span><div class="d-flex gap-3"><a href="{{ route('admin.settings') }}">Settings</a><a href="{{ route('site.home') }}" target="_blank" rel="noopener">Visit website ↗</a><a href="{{ route('admin.profile') }}">My Profile</a></div></footer></main></div>
<div class="modal fade" id="actionConfirmation" tabindex="-1" aria-labelledby="confirmTitle" aria-describedby="confirmMessage" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="confirmTitle" data-confirm-title>Confirm action</h2><button class="btn-close" data-bs-dismiss="modal" aria-label="Close confirmation"></button></div><div class="modal-body"><span class="confirm-symbol">@include('admin.icon',['name'=>'warning'])</span><p class="mb-0" id="confirmMessage" data-confirm-message></p></div><div class="modal-footer"><button class="btn" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn primary" type="button" data-confirm-submit>Confirm</button></div></div></div></div>
@else @yield('content') @endif
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',function(){const errors=@json($errors->messages());Object.entries(errors).forEach(function([key,messages]){const name=key.replace(/\.(\d+)/g,'[$1]');const controls=Array.from(document.querySelectorAll('input,textarea,select'));const control=controls.find(function(input){return input.name===name||input.name===name+'[]'||input.name===key.split('.')[0]+'[]';});if(!control)return;control.setAttribute('aria-invalid','true');const field=control.closest('.field');if(field&&!field.querySelector('.error')){const small=document.createElement('small');small.className='error text-danger d-block';small.setAttribute('role','alert');small.textContent=messages[0];field.appendChild(small);}});});</script>
@endif
<script src="{{ asset('assets/backend/js/bootstrap.bundle.min.js') }}" defer></script>
<script src="{{ asset('assets/backend/js/panel.js') }}?v=1" defer></script>
@stack('scripts')
<script src="{{ asset('assets/backend/js/filters.js') }}" defer></script>
</body></html>
