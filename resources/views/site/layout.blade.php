@php
    $title = isset($pageTitle) && !isset($blog) ? $pageTitle.' · '.$siteName : $seoTitle;
    if (request()->routeIs('site.blogs') && request()->integer('page') > 1) $canonical .= '?page='.request()->integer('page');
    $socials = collect(['facebook_url'=>'Facebook','instagram_url'=>'Instagram','x_url'=>'X','linkedin_url'=>'LinkedIn','youtube_url'=>'YouTube'])->filter(fn($label,$key) => filled($setting->$key));
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    @if($seoKeywords)<meta name="keywords" content="{{ $seoKeywords }}">@endif
    <meta name="robots" content="{{ ($noindex ?? false) ? 'noindex, nofollow' : 'index, follow, max-image-preview:large' }}">
    <meta name="theme-color" content="#1735c7"><link rel="canonical" href="{{ $canonical }}">
    <meta property="og:type" content="{{ ($isArticle ?? false) ? 'article' : 'website' }}">
    <meta property="og:site_name" content="{{ $siteName }}"><meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $seoDescription }}"><meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}"><meta property="og:locale" content="en_IN">
    <meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $seoDescription }}"><meta name="twitter:image" content="{{ $ogImage }}">
    @if(isset($blog))<meta property="article:published_time" content="{{ $blog->created_at->toAtomString() }}"><meta property="article:modified_time" content="{{ $blog->updated_at->toAtomString() }}">@endif
    <link rel="icon" href="{{ asset('assets/frontend/images/app-icon.webp') }}" type="image/webp">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/website.css') }}?v=2">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/bootstrap-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/reference.css') }}?v=1">
    <script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'WebSite','name'=>$siteName,'url'=>rtrim($setting->site_url ?: url('/'),'/').'/'], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @if(isset($blog))<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>$blog->title,'description'=>$seoDescription,'image'=>$ogImage,'datePublished'=>$blog->created_at->toAtomString(),'dateModified'=>$blog->updated_at->toAtomString(),'author'=>['@type'=>'Organization','name'=>$siteName],'mainEntityOfPage'=>$canonical], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endif
    @if(request()->routeIs('site.faq'))<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>collect($faq)->map(fn($item)=>['@type'=>'Question','name'=>$item[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$item[1]]])->all()], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endif
<link rel="stylesheet" href="{{ asset('assets/frontend/css/roboto.css') }}">
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
<div class="announcement"><div class="wrap"><div class="announcement-points"><span>Android app</span><span>Location history</span><span>Offline sync</span></div><div class="announcement-contact">@if($setting->support_email)<a href="mailto:{{ $setting->support_email }}">@include('site.icon',['name'=>'mail']) {{ $setting->support_email }}</a>@endif @if($setting->phone)<a href="tel:{{ preg_replace('/[^+0-9]/','',$setting->phone) }}">{{ $setting->phone }}</a>@endif @foreach($socials->take(2) as $key=>$label)<a class="announcement-social" href="{{ $setting->$key }}" target="_blank" rel="noopener noreferrer">{{ $label }}</a>@endforeach</div></div></div>
<header class="site-header"><nav class="navbar navbar-expand-lg wrap" aria-label="Main navigation">
<a class="brand navbar-brand" href="{{ route('site.home') }}">@if($setting->logo_url)<img src="{{ $setting->logo_url }}" alt="" width="38" height="40">@else<span class="brand-icon">@include('site.icon',['name'=>'pin'])</span>@endif<span>{{ $siteName }}<small>YOUR PLACES. CLEARLY CONNECTED.</small></span></a>
<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#siteNavigation" aria-controls="siteNavigation" aria-expanded="false" aria-label="Open navigation">☰</button>
<div class="collapse navbar-collapse" id="siteNavigation"><ul class="navbar-nav">@foreach(['home'=>'Home','about'=>'About us','features'=>'Features','faq'=>'FAQ','blogs'=>'Blog','contact'=>'Contact'] as $route=>$label)<li class="nav-item"><a class="nav-link {{ request()->routeIs('site.'.$route)?'active':'' }}" @if(request()->routeIs('site.'.$route)) aria-current="page" @endif href="{{ route('site.'.$route) }}">{{ $label }}</a></li>@endforeach</ul><a class="button compact header-download btn btn-primary" href="{{ route('site.download') }}">@include('site.icon',['name'=>'download']) <span>Download app</span></a></div>
</nav></header>
<main id="main">@unless(request()->routeIs('site.home'))<nav class="wrap site-breadcrumb" aria-label="Breadcrumb"><a href="{{ route('site.home') }}">Home</a><span aria-hidden="true">/</span><span>{{ $pageTitle ?? $siteName }}</span></nav>@endunless @yield('content')</main>
<footer class="site-footer"><div class="wrap footer-grid">
<div class="footer-brand"><a class="brand" href="{{ route('site.home') }}">@if($setting->logo_url)<img src="{{ $setting->logo_url }}" alt="" width="38" height="40">@else<span class="brand-icon">@include('site.icon',['name'=>'pin'])</span>@endif<span>{{ $siteName }}</span></a><p>Your current place, meaningful history and the people you connect with. A clearer view of your day.</p>@if($socials->isNotEmpty())<div class="footer-socials">@foreach($socials as $key=>$label)<a href="{{ $setting->$key }}" aria-label="{{ $label }}" title="{{ $label }}" target="_blank" rel="noopener noreferrer">{{ ['Facebook'=>'f','Instagram'=>'ig','X'=>'X','LinkedIn'=>'in','YouTube'=>'yt'][$label] }}</a>@endforeach</div>@endif</div>
<div><h2>Quick links</h2>@foreach(['home'=>'Home','about'=>'About us','features'=>'Features','blogs'=>'Blog','contact'=>'Contact us'] as $route=>$label)<a href="{{ route('site.'.$route) }}">{{ $label }}</a>@endforeach</div>
<div><h2>Inside the app</h2>@foreach(['Current location','Location history','Stay duration','Connected members','Offline sync'] as $label)<a href="{{ route('site.features') }}">{{ $label }}</a>@endforeach</div>
<div><h2>Help & support</h2><a href="{{ route('site.faq') }}">FAQs</a>@foreach($footerPages as $footerPage)<a href="{{ route('site.page',$footerPage->slug) }}">{{ $footerPage->title }}</a>@endforeach @if($setting->support_email)<a class="break" href="mailto:{{ $setting->support_email }}">{{ $setting->support_email }}</a>@endif @if($setting->phone)<a href="tel:{{ preg_replace('/[^+0-9]/','',$setting->phone) }}">{{ $setting->phone }}</a>@endif @if($setting->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/','',$setting->whatsapp) }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>@endif @if(!$setting->support_email && !$setting->phone && !$setting->whatsapp)<a href="{{ route('site.contact') }}">Contact support</a>@endif</div>
<div class="footer-showcase"><h2>Your day, in view</h2><a class="footer-preview" href="{{ route('site.features') }}" aria-label="Explore the Android app"><span>Your places.<br>Your time.<br>Your connections.</span><img src="{{ asset('assets/frontend/images/phone-preview.svg') }}" alt="" width="370" height="740" loading="lazy"></a><a class="footer-download" href="{{ route('site.download') }}">@include('site.icon',['name'=>'download']) Download Android app</a></div>
</div><div class="wrap footer-bottom"><span>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</span><div class="footer-legal"><a href="{{ route('site.sitemap') }}">Sitemap</a><a href="{{ route('site.contact') }}">Contact us</a><span>Made for Android.</span></div></div></footer>
<script src="{{ asset('assets/frontend/js/bootstrap.bundle.min.js') }}" defer></script><script src="{{ asset('assets/frontend/js/reference.js') }}?v=1" defer></script>
</body></html>
