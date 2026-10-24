@extends('admin.layout')
@section('title', 'Sliders')
@section('content')
<div class="card" style="max-width:760px"><h2 style="margin-top:0">Add images</h2><p class="muted">Upload up to five images. Active sliders appear in the Android app.</p>
    <form method="post" action="{{ route('admin.sliders.store') }}" enctype="multipart/form-data">@csrf
        <div class="form-grid"><div class="field"><label for="images">Images</label><input class="form-control" id="images" type="file" name="images[]" accept="image/jpeg,image/png,image/webp" multiple required><small>Each image can be up to 2 MB.</small></div><div class="field"><label for="title">Title (optional)</label><input class="form-control" id="title" name="title" maxlength="100" value="{{ old('title') }}"></div></div>
        <div class="form-actions"><button class="btn primary" type="submit">Add sliders</button></div>
    </form>
</div>
<div class="section-heading"><h2>{{ $sliders->count() }} slider{{ $sliders->count()===1?'':'s' }}</h2></div>
<div class="slider-grid">
@forelse($sliders as $slider)
<div class="card"><img src="{{ route('admin.sliders.image',$slider) }}" alt="{{ $slider->title ?: 'Slider image' }}"><form method="post" action="{{ route('admin.sliders.update',$slider) }}" enctype="multipart/form-data">@csrf @method('PATCH')
    <div class="field"><label for="title_{{ $slider->id }}">Title</label><input class="form-control" id="title_{{ $slider->id }}" name="title" maxlength="100" value="{{ $slider->title }}"></div>
    <div class="field"><label for="order_{{ $slider->id }}">Display order</label><input class="form-control" id="order_{{ $slider->id }}" type="number" name="sort_order" min="0" max="100000" value="{{ $slider->sort_order }}" required></div>
    <div class="field"><label for="image_{{ $slider->id }}">Replace image (optional)</label><input class="form-control" id="image_{{ $slider->id }}" type="file" name="image" accept="image/jpeg,image/png,image/webp"></div>
    <label class="check" style="margin-top:13px"><input type="checkbox" name="is_active" value="1" @checked($slider->is_active)> Active in app</label>
    <div class="form-actions"><button class="btn primary" type="submit">Save slider</button></div>
</form><form method="post" action="{{ route('admin.sliders.destroy',$slider) }}" data-confirm="Delete this slider?" style="margin-top:10px">@csrf @method('DELETE')<button class="btn danger" type="submit">Delete slider</button></form></div>
@empty<div class="card empty">No sliders yet. Upload an image above to get started.</div>@endforelse
</div>
@endsection

