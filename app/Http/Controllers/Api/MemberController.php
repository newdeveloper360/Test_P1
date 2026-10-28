<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\LocationHistoryFilters;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MemberController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if ($request->user()->role !== 'user') {
            return Response::failed('User access required.', null, 403);
        }
        $data = $request->validate(['name' => ['nullable', 'string', 'max:100'], 'phone' => ['nullable', 'string', 'max:13', 'regex:/^[0-9]+$/']]);
        $members = $request->user()->members()
            ->when($request->filled('name'), fn ($query) => $query->whereLike('name', '%'.$data['name'].'%'))
            ->when($request->filled('phone'), fn ($query) => $query->whereLike('phone', '%'.$data['phone'].'%'))
            ->with('latestFix')
            ->orderByDesc('created_at')->orderByDesc('id')->paginate(100)->withQueryString()
            ->through(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'is_blocked' => $member->is_blocked,
                'is_active' => $member->is_active,
                'joined_at' => $member->created_at,
                'latest_location' => $member->latestFix,
            ]);

        return Response::success('Members loaded.', $members);
    }

    public function locations(Request $request, User $member): JsonResponse
    {
        if ($request->user()->role !== 'user'
            || $member->role !== 'member'
            || $member->invited_by_user_id !== $request->user()->id) {
            return Response::failed('Member not found.', null, 404);
        }

        $query = LocationHistoryFilters::apply($member->locations(), $request);

        return Response::success('Member location history loaded.', $query
            ->orderByDesc('captured_at')->orderByDesc('id')->paginate(50)->withQueryString());
    }
}
