@extends('site.layout')
@section('content')
<section class="new-hero">
    <div class="hero-grid wrap">
        <div class="hero-copy">
            <span class="hero-kicker">A LITTLE MORE CLARITY. EVERY DAY.</span>
            <h1>Know your places.<br><span class="typing-line" aria-hidden="true"><span data-typing>Stay connected.</span><span class="typing-caret"></span></span><span class="visually-hidden">Stay connected.</span></h1>
            <p class="hero-description">Your location, your time, your connections.<br> Bring your day into view with {{ $siteName }} for Android.</p>
            <div class="hero-actions"><a class="button btn btn-primary" href="{{ route('site.download') }}">@include('site.icon',['name'=>'download']) Download app</a><a class="button secondary btn" href="{{ route('site.features') }}">Explore features @include('site.icon',['name'=>'arrow'])</a></div>
            <div class="hero-assurance">@include('site.icon',['name'=>'shield']) Location sharing starts with your permission.</div>
            <div class="hero-platform"><span class="platform-mark">@include('site.icon',['name'=>'phone'])</span><span><strong>Made for Android</strong><small>Current places. Meaningful history.</small></span></div>
        </div>
        <div class="product-scene">
            <span class="scene-circle circle-outer"></span><span class="scene-circle circle-inner"></span><span class="scene-dots"></span>
            <img class="hero-phone" src="{{ asset('assets/frontend/images/phone-preview.svg') }}" alt="Phone mockup showing the app's location details, map preview, stay time and navigation" width="370" height="740" fetchpriority="high">
            <div class="product-float float-place"><span class="float-icon">@include('site.icon',['name'=>'pin'])</span><div><strong>Your current place</strong><small>More than coordinates</small></div></div>
            <div class="product-float float-time"><span class="float-icon">@include('site.icon',['name'=>'clock'])</span><div><strong>Time, in context</strong><small>Understand each stay</small></div></div>
            <div class="product-float float-sync"><span class="float-icon">@include('site.icon',['name'=>'sync'])</span><div><strong>Offline? Keep going.</strong><small>Sync when connected</small></div></div>
            <span class="scene-caption">Illustrative app preview</span>
        </div>
    </div>
</section>
<section class="capability-bar wrap" aria-label="App highlights">
    @foreach([['pin','Your place','Address & coordinates'],['clock','Your time','History & stay duration'],['people','Your connections','Users & members'],['sync','Your way','Offline sync']] as [$icon,$heading,$copy])
    <div class="capability"><span>@include('site.icon',['name'=>$icon])</span><div><strong>{{ $heading }}</strong><small>{{ $copy }}</small></div></div>
    @endforeach
</section>
<section class="section wrap">
    <div class="section-heading"><div><span class="eyebrow">// BUILT AROUND YOUR DAY</span><h2>Small details.<br><span>A clearer picture.</span></h2></div><a class="button compact btn btn-primary" href="{{ route('site.features') }}">View all features @include('site.icon',['name'=>'arrow'])</a></div>
    <div class="feature-grid">
    @foreach([['pin','A place you can recognise','See available area, city, district, state, PIN code and country, alongside coordinates and reported accuracy.'],['clock','Every stop has a story','Look back at recorded address changes, their dates and the estimated time spent at each place.'],['people','Better, connected','Invite members using your six-digit code and view the location records they share with your account.']] as [$icon,$heading,$copy])
    <article class="feature-card card"><span class="icon-tile">@include('site.icon',['name'=>$icon])</span><span class="feature-number">0{{ $loop->iteration }}</span><h3>{{ $heading }}</h3><p>{{ $copy }}</p><a class="text-link" href="{{ route('site.features') }}">Learn more @include('site.icon',['name'=>'arrow'])</a></article>
    @endforeach
    </div>
</section>
<section class="section story-section"><div class="wrap split">
    <div class="history-demo"><div class="demo-heading"><div><small>A LOOK AT YOUR DAY</small><h3>Your location history</h3></div><span class="demo-pill">Timeline</span></div><div class="history-line">
    @foreach([['pin','Arrive at a place','A suitable reading starts a record.','01'],['clock','Make time for your day','Stay duration gives each place context.','02'],['arrow','On to your next stop','A recorded address change joins your history.','03']] as [$icon,$heading,$copy,$step])<div class="history-stop"><span class="stop-icon">@include('site.icon',['name'=>$icon])</span><div><strong>{{ $heading }}</strong><p>{{ $copy }}</p></div><small>{{ $step }}</small></div>@endforeach
    </div><div class="demo-footer">@include('site.icon',['name'=>'sync']) Pending changes sync when your connection returns.</div></div>
    <div class="story-copy"><span class="eyebrow">// FOLLOW THE MOMENTS BETWEEN</span><h2>Your day.<br>A little easier to <span>understand.</span></h2><p class="lead small">An address tells you where. Stay time helps explain the moment. Your history brings them together in a view you can return to.</p><ul class="check-list"><li>Recorded dates and original capture times</li><li>Location details organised in one place</li><li>Open a recorded point in Google Maps</li></ul><a class="text-link" href="{{ route('site.features') }}">Explore location history @include('site.icon',['name'=>'arrow'])</a></div>
</div></section>
<section class="section wrap connection-section"><div class="section-heading"><div><span class="eyebrow">// A CONNECTION THAT MAKES SENSE</span><h2>One app.<br><span>Your own view.</span></h2></div><p>Create your account as a user, or join someone you trust as a member. Each role gets a view built around it.</p></div><div class="row g-4"><div class="col-md-6"><article class="role-card card h-100"><span class="role-tag">FOR USERS</span><span class="icon-tile">@include('site.icon',['name'=>'people'])</span><h3>Bring your circle together.</h3><p>Share your invite code, see connected members and review the location history shared with you.</p><a class="text-link" href="{{ route('site.download') }}">Get started in the app @include('site.icon',['name'=>'arrow'])</a></article></div><div class="col-md-6"><article class="role-card card h-100"><span class="role-tag">FOR MEMBERS</span><span class="icon-tile">@include('site.icon',['name'=>'pin'])</span><h3>Your places, at a glance.</h3><p>Join with an invite code and view your own current location, place history and account details.</p><a class="text-link" href="{{ route('site.faq') }}">How sharing works @include('site.icon',['name'=>'arrow'])</a></article></div></div></section>
<section class="section soft"><div class="wrap"><div class="centered section-title"><span class="eyebrow">// READY WHEN YOU ARE</span><h2>A simple start.<br><span>A more connected day.</span></h2></div><div class="steps">@foreach([['Download the app','Get the Android APK and install it on your phone.'],['Make your connection','Create a user account or join as a member with an invite code.'],['Choose your permissions','Allow location access and see your available location details.']] as [$heading,$copy])<article class="card"><span class="step-number">0{{ $loop->iteration }}</span><h3>{{ $heading }}</h3><p>{{ $copy }}</p></article>@endforeach</div></div></section>
@if($blogs->isNotEmpty())<section class="section wrap"><div class="section-heading"><div><span class="eyebrow">// FROM THE JOURNAL</span><h2>A little more to explore.</h2></div><a class="text-link" href="{{ route('site.blogs') }}">All articles @include('site.icon',['name'=>'arrow'])</a></div><div class="blog-grid">@foreach($blogs as $article)@include('site.blog-card')@endforeach</div></section>@endif
<section class="section wrap home-questions"><div><span class="eyebrow">// BEFORE YOU GET STARTED</span><h2>A few helpful<br><span>answers.</span></h2><p>Location, permissions and the details in between.</p><a class="text-link" href="{{ route('site.faq') }}">Read all FAQs @include('site.icon',['name'=>'arrow'])</a></div><div class="faq-list">@foreach(array_slice($faq,0,3) as [$question,$answer])<details><summary>{{ $question }}<span>+</span></summary><p>{{ $answer }}</p></details>@endforeach</div></section>
@include('site.cta')
@endsection

