<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])]]);

        return view('admin.contacts', ['messages' => ContactMessage::when($data['status'] ?? null,
            fn ($query, $status) => $query->where('status', $status))
            ->when($data['q'] ?? null, fn ($q, $term) => $q->where(fn ($q) => $q->whereLike('name', '%'.$term.'%')->orWhereLike('email', '%'.$term.'%')->orWhereLike('subject', '%'.$term.'%')))
            ->latest()->paginate(20)->withQueryString()]);
    }

    public function update(Request $request, ContactMessage $contact)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['approved', 'rejected'])]]);
        $contact->update($data);

        return back()->with('success', 'Contact request '.$data['status'].'.');
    }
}
