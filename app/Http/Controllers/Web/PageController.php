<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Support\CleanHtml;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index() { return view('admin.pages.index', ['pages' => Page::latest()->paginate(20)]); }
    public function create() { return view('admin.pages.form', ['page' => new Page]); }
    public function edit(Page $page) { return view('admin.pages.form', compact('page')); }

    private function data(Request $request, ?Page $page = null): array
    {
        if (!$request->filled('slug')) $request->merge(['slug' => Str::slug($request->input('title', ''))]);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['required', 'string', 'max:180', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages', 'slug')->ignore($page)],
            'content' => ['required', 'string', 'max:200000'],
        ]);
        $data['content'] = CleanHtml::make($data['content']);
        if (trim(strip_tags($data['content'])) === '') throw \Illuminate\Validation\ValidationException::withMessages(['content' => 'Enter some readable page content.']);
        return $data;
    }
    public function store(Request $request)
    {
        Page::create($this->data($request));
        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }
    public function update(Request $request, Page $page)
    {
        $page->update($this->data($request, $page));
        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }
    public function destroy(Page $page)
    {
        $page->delete();
        return back()->with('success', 'Page deleted.');
    }
}
