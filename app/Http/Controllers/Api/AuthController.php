<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        if (Setting::current()->maintenance_mode)
            return Response::failed(Setting::current()->maintenance_message
                ?: 'The app is under maintenance. Please try again later.', null, 503);
        $request->merge(['phone' => $this->normalizePhone((string) $request->input('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[6-9][0-9]{9}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'role' => ['sometimes', Rule::in(['user', 'member'])],
            'invite_code' => ['required_if:role,member', 'nullable', 'regex:/^[0-9]{6}$/'],
        ]);

        $role = $data['role'] ?? 'user';
        if ($role === 'member') {
            $inviter = User::query()->where('role', 'user')
                ->where('invite_code', $data['invite_code'])
                ->where('is_blocked', false)->where('is_active', true)->first();
            if (!$inviter) return Response::failed('Invalid invite code.', null, 422);
            $data['invited_by_user_id'] = $inviter->id;
            unset($data['invite_code']);
        } else {
            $data['invite_code'] = $this->newInviteCode();
        }
        $data['role'] = $role;
        $user = User::create($data);
        $user->refresh();
        $this->recordLogin($user, $request);

        return Response::success('Account created successfully.', $this->credentials($user), 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,13}$/'],
            'password' => ['required', 'string', 'regex:/^[0-9]{6}$/'],
        ]);
        $normalized = $this->normalizePhone($data['phone']);
        $user = User::where('phone', $normalized)->first();
        if (!$user && $normalized !== $data['phone'])
            $user = User::where('phone', $data['phone'])->first();
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return Response::failed('Invalid phone or password.', null, 401);
        }
        if ($user->role === 'admin') {
            return Response::failed('Admin accounts sign in on the Laravel website.', null, 403);
        }
        if ($user->is_blocked) return Response::failed('This account is blocked.', null, 403);
        if (!$user->is_active) return Response::failed('This account is inactive. Contact support.', null, 403);
        if (Setting::current()->maintenance_mode)
            return Response::failed(Setting::current()->maintenance_message
                ?: 'The app is under maintenance. Please try again later.', null, 503);

        $this->recordLogin($user, $request);
        return Response::success('Signed in successfully.', $this->credentials($user));
    }

    public function me(Request $request): JsonResponse
    {
        return Response::success('Profile loaded.', ['user' => $this->publicUser($request->user())]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->tokenCan('profile:write')) {
            return Response::failed('This session can only view the profile.', null, 403);
        }
        $request->merge(['phone' => $this->normalizePhone((string) $request->input('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', $user->role === 'admin'
                ? 'regex:/^[0-9]{10}$/' : 'regex:/^[6-9][0-9]{9}$/',
                Rule::unique('users', 'phone')->ignore($user->id)],
        ]);
        $user->update($data);
        return Response::success('Profile updated.', ['user' => $this->publicUser($user->refresh())]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user->tokenCan('profile:write')) {
            return Response::failed('This session cannot change a password.', null, 403);
        }
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', 'regex:/^[0-9]{6}$/'],
        ]);
        if (!Hash::check($data['current_password'], $user->password)) {
            return Response::failed('Current password is incorrect.', null, 422);
        }
        $user->update(['password' => $data['password']]);
        return Response::success('Password updated.');
    }

    public function loginHistory(Request $request): JsonResponse
    {
        return Response::success('Login history loaded.', $request->user()->loginEvents()
            ->orderByDesc('logged_in_at')->paginate(50));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        return Response::success('Signed out.');
    }

    private function newInviteCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (User::where('invite_code', $code)->exists());
        return $code;
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[\s\-()]/', '', trim($phone));
        return str_starts_with($phone, '+91') && strlen($phone) === 13
            ? substr($phone, 3) : $phone;
    }

    private function recordLogin(User $user, Request $request): void
    {
        $user->loginEvents()->create([
            'ip_address' => $request->ip(),
            'device' => substr((string) $request->userAgent(), 0, 255),
            'logged_in_at' => now(),
        ]);
    }

    private function credentials(User $user): array
    {
        return [
            'token' => $user->createToken('android-app')->plainTextToken,
            'user' => $this->publicUser($user),
        ];
    }

    private function publicUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'role' => $user->role,
            'is_blocked' => $user->is_blocked,
            'invite_code' => $user->role === 'user' ? $user->invite_code : null,
            'invited_by_user_id' => $user->invited_by_user_id,
            'member_count' => $user->role === 'user' ? $user->members()->count() : 0,
            'created_at' => $user->created_at,
        ];
    }
}
