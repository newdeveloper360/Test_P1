<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\CleanHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function index(Request $request) {
        $data = $request->validate(['q'=>['nullable','string','max:100'], 'status'=>['nullable',Rule::in(['published','draft'])]]);
        $query = Blog::select(['id','title','slug','is_published','created_at']);
        if (!empty($data['q'])) $query->where('title','like','%'.$data['q'].'%');
        if (!empty($data['status'])) $query->where('is_published',$data['status']==='published');
        return view('admin.blogs.index', ['blogs'=>$query->latest()->paginate(20)->withQueryString()]);
    }
    public function create() { return view('admin.blogs.form', ['blog' => new Blog]); }
    public function edit(Blog $blog) { return view('admin.blogs.form', compact('blog')); }

    private function data(Request $request, ?Blog $blog = null): array
    {
        if (!$request->filled('slug')) $request->merge(['slug' => Str::slug($request->input('title', ''))]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blogs', 'slug')->ignore($blog)],
            'short_description' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:200000'],
            'featured_image' => [$blog ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=6000,max_height=6000'],
            'meta_title' => ['nullable', 'string', 'max:180'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'is_published' => ['sometimes', 'boolean'],
        ]);
        $data['content'] = CleanHtml::make($data['content']);
        if (trim(strip_tags($data['content'])) === '') {
            throw \Illuminate\Validation\ValidationException::withMessages(['content' => 'Enter some readable blog content.']);
        }
        unset($data['featured_image']);
        if ($request->hasFile('featured_image')) {
            $data['featured_image_data'] = base64_encode($request->file('featured_image')->get());
            $data['featured_image_mime'] = $request->file('featured_image')->getMimeType();
        }
        $data['is_published'] = $request->boolean('is_published');
        return $data;
    }

    public function store(Request $request)
    {
        Blog::create($this->data($request));
        return redirect()->route('admin.blogs.index')->with('success', 'Blog created.');
    }

    public function update(Request $request, Blog $blog)
    {
        $blog->update($this->data($request, $blog));
        return redirect()->route('admin.blogs.index')->with('success', 'Blog updated.');
    }

    public function destroy(Blog $blog)
    {
        $blog->delete();
        return back()->with('success', 'Blog deleted.');
    }
}
