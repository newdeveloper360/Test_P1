<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
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
        $members = $request->user()->members()
            ->with(['locations' => fn ($query) => $query->orderByDesc('captured_at')->limit(1)])
            ->orderByDesc('created_at')->paginate(100)
            ->through(fn (User $member) => [
                'id' => $member->id,
                'name' => $member->name,
                'phone' => $member->phone,
                'is_blocked' => $member->is_blocked,
                'joined_at' => $member->created_at,
                'latest_location' => $member->locations->first(),
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

        return Response::success('Member location history loaded.', $member->locations()
            ->orderByDesc('captured_at')->orderByDesc('id')->paginate(50));
    }
}
