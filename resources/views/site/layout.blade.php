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
    <link rel="icon" href="{{ asset('site/app-icon.webp') }}" type="image/webp">
    <link rel="stylesheet" href="{{ asset('site/website.css') }}?v=1">
    <script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'WebSite','name'=>$siteName,'url'=>rtrim($setting->site_url ?: url('/'),'/').'/'], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
    @if(isset($blog))<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'BlogPosting','headline'=>$blog->title,'description'=>$seoDescription,'image'=>$ogImage,'datePublished'=>$blog->created_at->toAtomString(),'dateModified'=>$blog->updated_at->toAtomString(),'author'=>['@type'=>'Organization','name'=>$siteName],'mainEntityOfPage'=>$canonical], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endif
    @if(request()->routeIs('site.faq'))<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>collect($faq)->map(fn($item)=>['@type'=>'Question','name'=>$item[0],'acceptedAnswer'=>['@type'=>'Answer','text'=>$item[1]]])->all()], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>@endif
</head>
<body>
<a class="skip" href="#main">Skip to content</a>
@if($setting->phone || $setting->support_email)<div class="contact-strip"><div class="wrap"><span>Your places. Your connections.</span><div>@if($setting->phone)<a href="tel:{{ preg_replace('/[^+0-9]/','',$setting->phone) }}">{{ $setting->phone }}</a>@endif @if($setting->support_email)<a href="mailto:{{ $setting->support_email }}">{{ $setting->support_email }}</a>@endif</div></div></div>@endif
<header class="site-header"><div class="wrap header-inner">
    <a class="brand" href="{{ route('site.home') }}">@if($setting->logo_url)<img src="{{ $setting->logo_url }}" alt="" width="40" height="40">@else<span class="brand-icon">@include('site.icon',['name'=>'pin'])</span>@endif<span>{{ $siteName }}<small>STAY CONNECTED</small></span></a>
    <nav class="desktop-nav" aria-label="Main navigation">@foreach(['home'=>'Home','about'=>'About','features'=>'Features','faq'=>'FAQ','blogs'=>'Blog','contact'=>'Contact'] as $route=>$label)<a @if(request()->routeIs('site.'.$route)) aria-current="page" @endif href="{{ route('site.'.$route) }}">{{ $label }}</a>@endforeach</nav>
    <a class="button compact header-download" href="{{ route('site.download') }}">@include('site.icon',['name'=>'download']) Download app</a>
    <details class="mobile-menu"><summary aria-label="Open navigation">☰</summary><nav aria-label="Mobile navigation">@foreach(['home'=>'Home','about'=>'About','features'=>'Features','faq'=>'FAQ','blogs'=>'Blog','contact'=>'Contact'] as $route=>$label)<a href="{{ route('site.'.$route) }}">{{ $label }}</a>@endforeach<a href="{{ route('site.download') }}">Download app</a></nav></details>
</div></header>
<main id="main">@yield('content')</main>
<footer class="site-footer"><div class="wrap footer-grid"><div class="footer-brand"><a class="brand" href="{{ route('site.home') }}"><span class="brand-icon">@include('site.icon',['name'=>'pin'])</span>{{ $siteName }}</a><p>A clearer picture of your places,<br>and the people you connect with.</p><a class="text-link" href="{{ route('site.download') }}">Get the Android app @include('site.icon',['name'=>'arrow'])</a></div><div><h2>Explore</h2>@foreach(['about'=>'About us','features'=>'Features','faq'=>'FAQs','blogs'=>'Blog'] as $route=>$label)<a href="{{ route('site.'.$route) }}">{{ $label }}</a>@endforeach</div><div><h2>Support</h2><a href="{{ route('site.contact') }}">Contact us</a>@foreach($footerPages as $footerPage)<a href="{{ route('site.page',$footerPage->slug) }}">{{ $footerPage->title }}</a>@endforeach</div><div><h2>Let’s connect</h2>@if($setting->support_email)<a class="break" href="mailto:{{ $setting->support_email }}">{{ $setting->support_email }}</a>@endif @if($setting->phone)<a href="tel:{{ preg_replace('/[^+0-9]/','',$setting->phone) }}">{{ $setting->phone }}</a>@endif @if($setting->whatsapp)<a href="https://wa.me/{{ preg_replace('/\D/','',$setting->whatsapp) }}" target="_blank" rel="noopener noreferrer">WhatsApp ↗</a>@endif @foreach($socials as $key=>$label)<a href="{{ $setting->$key }}" target="_blank" rel="noopener noreferrer">{{ $label }} ↗</a>@endforeach @if(!$setting->support_email && !$setting->phone && !$setting->whatsapp && $socials->isEmpty())<a href="{{ route('site.contact') }}">Send us a message →</a>@endif</div></div><div class="wrap footer-bottom"><span>© {{ date('Y') }} {{ $siteName }}. All rights reserved.</span><span>Made for Android. Built around your day.</span></div></footer>
</body></html>
