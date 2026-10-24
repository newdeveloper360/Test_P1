<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Slider;
use App\Models\User;
use App\Support\WebsiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function loginForm(): View|RedirectResponse
    {
        if (Auth::guard('web')->user()?->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'password' => ['required', 'string', 'min:6', 'max:255'],
        ]);
        if (!Auth::guard('web')->attempt([
            'phone' => $data['phone'], 'password' => $data['password'],
            'role' => 'admin', 'is_blocked' => false, 'is_active' => true,
        ])) {
            return back()->withErrors(['phone' => 'Invalid admin phone or password.'])
                ->onlyInput('phone');
        }
        $request->session()->regenerate();
        if (!preg_match('/^[0-9]{6}$/', $data['password'])) {
            $request->session()->put('require_password_update', true);
            return redirect()->route('admin.profile')->with('success', 'Please replace your older password with a 6-digit password to continue.');
        }
        $request->user()->loginEvents()->create([
            'ip_address' => $request->ip(),
            'device' => substr((string) $request->userAgent(), 0, 255),
            'logged_in_at' => now(),
        ]);
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    public function dashboard(): View
    {
        return view('admin.dashboard', [
            'active' => User::whereIn('role', ['user', 'member'])->where('is_active', true)->where('is_blocked', false)->count(),
            'sliderCount' => Slider::count(),
            'blogCount' => \App\Models\Blog::where('is_published', true)->count(),
            'pendingContacts' => \App\Models\ContactMessage::where('status', 'pending')->count(),
            'inactive' => User::whereIn('role', ['user', 'member'])->where('is_active', false)->count(),
            'newAdvice' => \App\Models\Advice::where('status', 'new')->count(),
            'totalUsers' => User::where('role', 'user')->count(),
            'totalMembers' => User::where('role', 'member')->count(),
            'blocked' => User::whereIn('role', ['user', 'member'])->where('is_blocked', true)->count(),
            'locations' => DB::table('user_locations')->count(),
            'recentAccounts' => User::whereIn('role', ['user', 'member'])
                ->withCount('members')->latest()->limit(6)->get(),
            'recentLocations' => DB::table('user_locations')
                ->join('users', 'users.id', '=', 'user_locations.user_id')
                ->select('user_locations.*', 'users.name as user_name', 'users.phone as user_phone')
                ->orderByDesc('user_locations.captured_at')->limit(6)->get(),
        ]);
    }

    public function accounts(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(['user', 'member'])],
            'status' => ['nullable', Rule::in(['active', 'inactive', 'blocked'])],
        ]);
        $query = User::whereIn('role', ['user', 'member']);
        if (!empty($filters['q'])) {
            $term = $filters['q'];
            $query->where(fn ($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%"));
        }
        if (!empty($filters['role'])) $query->where('role', $filters['role']);
        if (($filters['status'] ?? '') === 'blocked') $query->where('is_blocked', true);
        if (($filters['status'] ?? '') === 'inactive') $query->where('is_active', false);
        if (($filters['status'] ?? '') === 'active') $query->where('is_active', true)->where('is_blocked', false);
        return view('admin.accounts', [
            'accounts' => $query->with('invitedBy:id,name')
                ->withCount(['members', 'locations'])->withMax('locations', 'captured_at')
                ->orderByDesc('created_at')->paginate(20)->withQueryString(),
            'filters' => $filters,
        ]);
    }

    public function createAccount(): View
    {
        return view('admin.account-form', [
            'account' => null,
            'owners' => User::where('role', 'user')->where('is_blocked', false)->where('is_active', true)
                ->orderBy('name')->get(['id', 'name', 'phone']),
        ]);
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/', 'unique:users,phone'],
            'password' => ['required', 'regex:/^[0-9]{6}$/'],
            'role' => ['required', Rule::in(['user', 'member'])],
            'invited_by_user_id' => ['required_if:role,member', 'nullable', 'integer', 'exists:users,id'],
        ]);
        if ($data['role'] === 'user') {
            unset($data['invited_by_user_id']);
            $data['invite_code'] = $this->newInviteCode();
        } elseif (!User::whereKey($data['invited_by_user_id'])
            ->where('role', 'user')->where('is_blocked', false)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'invited_by_user_id' => 'Select an active user for this member.',
            ]);
        }
        $account = User::create($data);
        return redirect()->route('admin.accounts.show', $account)->with('success', 'Account created.');
    }

    public function showAccount(User $user): View
    {
        $this->assertAccount($user);
        return view('admin.account', [
            'account' => $user->load('invitedBy:id,name,phone')->loadCount(['members', 'locations']),
            'members' => $user->role === 'user' ? $user->members()->orderBy('name')->get() : collect(),
            'recentLocations' => $user->locations()->orderByDesc('captured_at')->limit(8)->get(),
            'loginEvents' => $user->loginEvents()->orderByDesc('logged_in_at')->limit(6)->get(),
        ]);
    }

    public function editAccount(User $user): View
    {
        $this->assertAccount($user);
        return view('admin.account-form', ['account' => $user, 'owners' => collect()]);
    }

    public function updateAccount(Request $request, User $user): RedirectResponse
    {
        $this->assertAccount($user);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/', Rule::unique('users', 'phone')->ignore($user)],
            'password' => ['nullable', 'regex:/^[0-9]{6}$/'],
        ]);
        if (empty($data['password'])) unset($data['password']);
        $user->update($data);
        return redirect()->route('admin.accounts.show', $user)->with('success', 'Account updated.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $this->assertAccount($user);
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $user->update(['is_active' => $data['is_active']]);
        if (!$user->is_active) $user->tokens()->delete();
        return back()->with('success', $user->is_active ? 'Account activated.' : 'Account deactivated. All app sessions signed out.');
    }

    public function toggleBlock(User $user): RedirectResponse
    {
        $this->assertAccount($user);
        $user->update(['is_blocked' => !$user->is_blocked]);
        if ($user->is_blocked) $user->tokens()->delete();
        return back()->with('success', $user->is_blocked ? 'Account blocked.' : 'Account unblocked.');
    }

    public function destroyAccount(User $user): RedirectResponse
    {
        $this->assertAccount($user);
        $user->tokens()->delete();
        $user->delete();
        return redirect()->route('admin.accounts.index')->with('success', 'Account deleted.');
    }

    public function locations(Request $request, User $user): View
    {
        $this->assertAccount($user);
        $data = $request->validate(['q'=>['nullable','string','max:100'], 'from'=>['nullable','date_format:Y-m-d'], 'to'=>['nullable','date_format:Y-m-d',Rule::when($request->filled('from'),'after_or_equal:from')]]);
        $query = $user->locations();
        if (!empty($data['q'])) $query->where(fn($q)=>$q->where('full_address','like','%'.$data['q'].'%')->orWhere('city','like','%'.$data['q'].'%'));
        if (!empty($data['from'])) $query->whereDate('captured_at','>=',$data['from']);
        if (!empty($data['to'])) $query->whereDate('captured_at','<=',$data['to']);
        return view('admin.locations', ['account'=>$user,'locations'=>$query->orderByDesc('captured_at')->orderByDesc('id')->paginate(30)->withQueryString()]);
    }

    public function settings(): View
    {
        return view('admin.settings', ['setting' => Setting::current()]);
    }

    public function clearLocations(User $user): RedirectResponse
    {
        $this->assertAccount($user);
        $count = DB::transaction(function () use ($user) {
            $account = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $account->forceFill(['locations_cleared_at' => now('UTC')])->save();
            return $account->locations()->delete();
        });
        return back()->with('success', $count.' location record(s) deleted for '.$user->name.'.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate(array_merge([
            'app_name' => ['required', 'string', 'max:60'],
            'banner_text' => ['nullable', 'string', 'max:200'],
            'maintenance_message' => ['nullable', 'string', 'max:200'],
            'android_version_code' => ['sometimes', 'required', 'integer', 'min:1', 'max:2100000000'],
            'android_update_url' => ['nullable', 'url', 'max:2048', 'starts_with:http://,https://'],
        ], WebsiteSettings::rules()));
        $data['maintenance_mode'] = $request->boolean('maintenance_mode');
        Setting::current()->update($data);
        return back()->with('success', 'App settings saved.');
    }

    public function uploadSettingImage(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['logo', 'banner'], true), 404);
        $request->validate(['image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);
        $path = $request->file('image')->store('app-branding', 'public');
        if (!$path) return back()->withErrors(['image' => 'Could not save the image.']);
        $setting = Setting::current();
        $pathField = $type === 'logo' ? 'logo_path' : 'banner_image_path';
        $urlField = $type === 'logo' ? 'logo_url' : 'banner_image_url';
        $oldPath = $setting->$pathField;
        $setting->$pathField = $path;
        $setting->$urlField = '/api/settings/image/'.$type.'?v='.time();
        $setting->save();
        if ($oldPath) Storage::disk('public')->delete($oldPath);
        return back()->with('success', ucfirst($type).' image updated.');
    }

    public function sliders(): View
    {
        return view('admin.sliders', ['sliders' => Slider::orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function sliderImage(Slider $slider)
    {
        abort_unless(Storage::disk('public')->exists($slider->image_path), 404);
        return Storage::disk('public')->response($slider->image_path);
    }

    public function storeSliders(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'images' => ['required', 'array', 'min:1', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'title' => ['nullable', 'string', 'max:100'],
        ]);
        $paths = [];
        try {
            DB::transaction(function () use ($request, $data, &$paths) {
                $order = (int) Slider::max('sort_order');
                foreach ($request->file('images') as $image) {
                    $path = $image->store('app-sliders', 'public');
                    if (!$path) throw new \RuntimeException('Could not save slider image.');
                    $paths[] = $path;
                    Slider::create(['image_path' => $path, 'title' => $data['title'] ?? null,
                        'sort_order' => ++$order, 'is_active' => true]);
                }
            });
        } catch (\Throwable $error) {
            foreach ($paths as $path) Storage::disk('public')->delete($path);
            return back()->withErrors(['images' => 'Could not save slider images.']);
        }
        return back()->with('success', count($paths).' slider image(s) added.');
    }

    public function updateSlider(Request $request, Slider $slider): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $oldPath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('app-sliders', 'public');
            if (!$path) return back()->withErrors(['image' => 'Could not save slider image.']);
            $oldPath = $slider->image_path;
            $data['image_path'] = $path;
        }
        $slider->update($data);
        if ($oldPath) Storage::disk('public')->delete($oldPath);
        return back()->with('success', 'Slider updated.');
    }

    public function destroySlider(Slider $slider): RedirectResponse
    {
        $path = $slider->image_path;
        $slider->delete();
        Storage::disk('public')->delete($path);
        return back()->with('success', 'Slider deleted.');
    }

    public function profile(): View
    {
        return view('admin.profile', ['admin' => Auth::guard('web')->user()]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = Auth::guard('web')->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^[0-9]{10}$/', Rule::unique('users', 'phone')->ignore($admin)],
            'current_password' => ['required_with:password', 'nullable', 'string'],
            'password' => [$request->session()->get('require_password_update') ? 'required' : 'nullable', 'confirmed', 'regex:/^[0-9]{6}$/'],
        ]);
        if (!empty($data['password'])) {
            if (!Hash::check($data['current_password'], $admin->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect.']);
            }
            $admin->password = $data['password'];
        }
        $admin->name = $data['name'];
        $admin->phone = $data['phone'];
        $admin->save();
        if (!empty($data['password'])) $request->session()->forget('require_password_update');
        return back()->with('success', 'Profile updated.');
    }

    private function assertAccount(User $user): void
    {
        abort_unless(in_array($user->role, ['user', 'member'], true), 404);
    }

    private function newInviteCode(): string
    {
        do {
            $code = (string) random_int(100000, 999999);
        } while (User::where('invite_code', $code)->exists());
        return $code;
    }
}
