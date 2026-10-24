<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1e35bc">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') · IP Tracker</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif;color:#172344;background:#f4f7ff}
        *{box-sizing:border-box}body{margin:0}a{color:inherit;text-decoration:none}button,input,select,textarea{font:inherit}button{cursor:pointer}
        .shell{min-height:100vh;display:grid;grid-template-columns:250px minmax(0,1fr)}.sidebar{background:#172da8;color:#fff;padding:28px 18px;display:flex;flex-direction:column;gap:30px}.brand{display:flex;align-items:center;gap:12px;font-size:19px;font-weight:800;letter-spacing:-.03em;padding:0 11px}.brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:12px;background:#fff;color:#233cc5;font-size:23px}.nav{display:grid;gap:7px}.nav a{padding:13px 14px;border-radius:12px;color:#dce4ff;font-weight:650}.nav a:hover,.nav a.active{background:#ffffff25;color:#fff}.sidebar-bottom{margin-top:auto;padding:16px 11px 0;border-top:1px solid #ffffff30}.sidebar-bottom small{display:block;color:#c6d1ff;margin:4px 0 15px}.logout{background:transparent;border:1px solid #ffffff75;color:#fff;border-radius:10px;padding:10px 14px;width:100%;text-align:left}
        .main{min-width:0;padding:28px clamp(18px,3vw,48px) 60px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:27px}.eyebrow{color:#65729a;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.1em}.page-title{font-size:clamp(27px,3vw,36px);line-height:1.13;margin:5px 0 0;letter-spacing:-.04em}.top-action{display:flex;gap:9px;flex-wrap:wrap;align-items:center}
        .card{background:#fff;border:1px solid #e2e8f6;border-radius:20px;box-shadow:0 8px 30px #1b38a80b;padding:22px}.grid{display:grid;gap:18px}.stats{grid-template-columns:repeat(4,minmax(0,1fr))}.two{grid-template-columns:repeat(2,minmax(0,1fr))}.stat .number{font-size:32px;font-weight:800;letter-spacing:-.05em;margin-top:13px}.stat .label{color:#677593;font-size:14px;font-weight:650}.stat .hint{color:#8794ac;font-size:12px;margin-top:4px}.section-heading{display:flex;align-items:center;justify-content:space-between;gap:14px;margin:30px 0 13px}.section-heading h2{font-size:20px;margin:0;letter-spacing:-.025em}
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;border:1px solid #d6dff3;background:#fff;color:#203674;border-radius:11px;padding:10px 15px;font-size:13px;font-weight:750;min-height:41px}.btn:hover{background:#f1f4ff}.btn.primary{background:#243bc5;border-color:#243bc5;color:#fff}.btn.primary:hover{background:#182ea9}.btn.danger{border-color:#f4c9cc;color:#a82834;background:#fff6f6}.btn.small{padding:7px 10px;min-height:33px}.btn.subtle{background:#f3f6ff}.actions{display:flex;gap:7px;align-items:center;flex-wrap:wrap}.actions form{display:inline}
        .table-wrap{overflow-x:auto}.table{width:100%;border-collapse:collapse;min-width:650px}.table th{text-align:left;color:#7683a2;font-size:12px;text-transform:uppercase;letter-spacing:.08em;font-weight:750;padding:12px 14px;border-bottom:1px solid #e8edf8}.table td{padding:14px;border-bottom:1px solid #edf1fa;vertical-align:middle;font-size:14px}.table tr:last-child td{border-bottom:0}.table a:not(.btn){color:#1f3bc0;font-weight:700}.muted{color:#7b87a2}.badge{display:inline-block;border-radius:999px;padding:5px 9px;font-size:11px;font-weight:800;background:#eef2ff;color:#2340b6}.badge.member{background:#f4ebff;color:#7840aa}.badge.blocked{background:#fff0f0;color:#b32940}.badge.active{background:#eaf8f2;color:#148466}.avatar{display:inline-grid;place-items:center;width:37px;height:37px;border-radius:12px;background:#eaf0ff;color:#213bc4;font-weight:800;margin-right:9px;vertical-align:middle}.empty{text-align:center;color:#7885a0;padding:35px 18px}.flash{padding:13px 16px;border-radius:12px;margin-bottom:20px;font-weight:650}.flash.success{background:#e7f7ef;color:#087951}.flash.error{background:#fff0f0;color:#a82834}
        .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:17px}.field{display:grid;gap:7px}.field.full{grid-column:1/-1}.field label{font-size:13px;font-weight:750;color:#354261}.field input,.field select,.field textarea,.search{width:100%;border:1px solid #d5deef;border-radius:11px;background:#fff;padding:11px 12px;outline:none;color:#1c2b4d}.field input:focus,.field select:focus,.field textarea:focus,.search:focus{border-color:#4261e6;box-shadow:0 0 0 3px #dfe6ff}.field textarea{min-height:88px;resize:vertical}.field small{color:#7b87a2}.field .error{font-size:12px;color:#b32940}.check{display:flex;gap:9px;align-items:center}.check input{width:auto}.form-actions{display:flex;gap:10px;margin-top:23px;flex-wrap:wrap}.filters{display:flex;gap:10px;align-items:end;flex-wrap:wrap}.filters .field{min-width:145px}.filters .grow{flex:1;min-width:220px}.pagination{margin-top:17px}.pagination nav{display:flex;justify-content:center}.pagination svg{width:18px}.pagination p{font-size:12px}
        .preview{width:100%;max-width:320px;max-height:160px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f6;background:#eff2fb}.slider-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px}.slider-grid img{width:100%;height:145px;object-fit:cover;border-radius:13px;background:#eef2ff}.slider-grid .field{margin-top:10px}.location-address{max-width:340px;white-space:normal}.login-wrap{min-height:100vh;display:grid;place-items:center;padding:20px;background:radial-gradient(circle at 15% 20%,#5367d8,transparent 34%),linear-gradient(135deg,#152996,#263ec8)}.login-card{width:min(100%,430px);padding:32px}.login-card h1{margin:21px 0 5px;font-size:27px;letter-spacing:-.04em}.login-card p{color:#7885a0;margin-bottom:27px}.login-card .field{margin-top:15px}.login-card .btn{width:100%;margin-top:22px}
        .mobile-logout{display:none}
        @media(max-width:900px){.shell{grid-template-columns:1fr}.sidebar{padding:14px 18px;gap:14px}.brand{font-size:17px}.nav{display:flex;overflow-x:auto}.nav a{white-space:nowrap;padding:9px 12px}.sidebar-bottom{display:none}.mobile-logout{display:block}.mobile-logout button{white-space:nowrap;color:#fff;background:#ffffff25;border:0;border-radius:12px;padding:9px 12px;font-weight:650}.main{padding-top:22px}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}}
        @media(max-width:600px){.stats,.two,.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.topbar{align-items:start;flex-direction:column}.card{padding:17px}.section-heading{align-items:start;flex-direction:column}}
    </style>
</head>
<body>
@if (Auth::guard('web')->user()?->role === 'admin')
<div class="shell">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">⌖</span><span>IP Tracker<br><small style="font-size:11px;font-weight:600;opacity:.7">ADMIN PANEL</small></span></a>
        <nav class="nav" aria-label="Admin navigation">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">◫ &nbsp; Overview</a>
            <a class="{{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}" href="{{ route('admin.accounts.index') }}">♙ &nbsp; Accounts</a>
            <a class="{{ request()->routeIs('admin.sliders*') ? 'active' : '' }}" href="{{ route('admin.sliders') }}">▣ &nbsp; Sliders</a>
            <a class="{{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}">Blog posts</a>
            <a class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}" href="{{ route('admin.pages.index') }}">Website pages</a>
            <a class="{{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}" href="{{ route('admin.contacts.index') }}">Contact requests</a>
            <a class="{{ request()->routeIs('admin.settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">⚙ &nbsp; Website & app settings</a>
            <a href="{{ route('site.home') }}" target="_blank" rel="noopener">View website ↗</a>
            <a class="{{ request()->routeIs('admin.profile*') ? 'active' : '' }}" href="{{ route('admin.profile') }}">● &nbsp; My profile</a>
            <form class="mobile-logout" action="{{ route('admin.logout') }}" method="post">@csrf<button type="submit">Sign out</button></form>
        </nav>
        <div class="sidebar-bottom"><strong>{{ Auth::guard('web')->user()->name }}</strong><small>Administrator</small><form action="{{ route('admin.logout') }}" method="post">@csrf<button class="logout" type="submit">Sign out →</button></form></div>
    </aside>
    <main class="main">
        <div class="topbar"><div><div class="eyebrow">Location management</div><h1 class="page-title">@yield('title', 'Overview')</h1></div><div class="top-action">@yield('actions')</div></div>
        @if(session('success'))<div class="flash success" role="status">{{ session('success') }}</div>@endif
        @if($errors->any())<div class="flash error" role="alert">{{ $errors->first() }}</div>@endif
        @yield('content')
    </main>
</div>
@else
    @yield('content')
@endif
@if($errors->any())
<script>document.addEventListener('DOMContentLoaded',function(){const errors=@json($errors->messages());Object.entries(errors).forEach(function([key,messages]){const name=key.replace(/\.(\d+)/g,'[$1]');const controls=Array.from(document.querySelectorAll('input,textarea,select'));const control=controls.find(function(input){return input.name===name||input.name===name+'[]'||input.name===key.split('.')[0]+'[]';});if(!control)return;control.setAttribute('aria-invalid','true');const field=control.closest('.field');if(field&&!field.querySelector('.error')){const small=document.createElement('small');small.className='error';small.setAttribute('role','alert');small.textContent=messages[0];field.appendChild(small);}});});</script>
@endif
@stack('scripts')
</body>
</html>
