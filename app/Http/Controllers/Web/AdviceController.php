<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Advice;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdviceController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['new', 'reviewed', 'planned', 'resolved'])],
            'role' => ['nullable', Rule::in(['user', 'member'])],
        ]);
        $query = Advice::with('user:id,name,phone,role');
        if (! empty($filters['q'])) {
            $term = $filters['q'];
            $query->where(fn ($q) => $q->whereLike('subject', "%{$term}%")
                ->orWhereLike('message', "%{$term}%")
                ->orWhereHas('user', fn ($u) => $u->whereLike('name', "%{$term}%")->orWhereLike('phone', "%{$term}%")));
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['role'])) {
            $query->whereHas('user', fn ($u) => $u->where('role', $filters['role']));
        }

        return view('admin.advice', ['advice' => $query->latest()->paginate(15)->withQueryString()]);
    }

    public function update(Request $request, Advice $advice)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['new', 'reviewed', 'planned', 'resolved'])],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $advice->update($data);

        return back()->with('success', 'Advice updated.');
    }
}
