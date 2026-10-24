@extends('site.layout')
@section('content')
<article class="article wrap"><a class="text-link" href="{{ route('site.blogs') }}">← Back to journal</a><header><span class="eyebrow">{{ $siteName }} JOURNAL · <time datetime="{{ $blog->created_at->toDateString() }}">{{ $blog->created_at->format('d M Y') }}</time></span><h1>{{ $blog->title }}</h1><p class="lead">{{ $blog->short_description }}</p></header><img class="article-cover" src="{{ route('site.blog.image',$blog) }}" alt="{{ $blog->title }}" width="1200" height="700"><div class="prose">{!! $blog->content !!}</div></article>@include('site.cta')
@endsection
