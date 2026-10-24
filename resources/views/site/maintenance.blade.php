@extends('site.layout')
@section('content')<section class="page-intro wrap centered"><span class="eyebrow">WE’LL BE BACK</span><h1>A little time to<br>make things better.</h1><p class="lead">{{ $setting->maintenance_message ?: 'We are doing some maintenance. Please try again later.' }}</p></section>@endsection
