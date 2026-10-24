@extends('site.layout')
@section('content')
<article class="article policy wrap"><header><span class="eyebrow">{{ $siteName }}</span><h1>{{ $page->title }}</h1><p class="muted">Last updated {{ $page->updated_at->format('d M Y') }}</p></header><div class="prose">{!! $page->content !!}</div></article>
@endsection
