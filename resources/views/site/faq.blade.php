@extends('site.layout')
@section('content')
<section class="page-intro wrap centered"><span class="eyebrow">GOOD TO KNOW</span><h1>A few helpful answers.</h1><p class="lead">Location, permissions, members and the details in between.</p></section><section class="faq-list wrap">@foreach($faq as [$question,$answer])<details @if($loop->first) open @endif><summary>{{ $question }}<span>+</span></summary><p>{{ $answer }}</p></details>@endforeach<div class="help-note"><h2>Still have a question?</h2><p>Send the support team a message. We’ll review your request.</p><a class="button secondary" href="{{ route('site.contact') }}">Contact us @include('site.icon',['name'=>'arrow'])</a></div></section>
@endsection
