<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $attributes = ['is_active' => true];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'is_blocked',
        'is_active',
        'invite_code',
        'invited_by_user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'show_password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'show_password' => 'encrypted',
            'is_blocked' => 'boolean',
            'is_active' => 'boolean',
            'locations_cleared_at' => 'datetime',
        ];
    }

    private ?string $pendingReadablePassword = null;

    public function setPasswordAttribute(#[\SensitiveParameter] string $value): void
    {
        $hashed = Hash::isHashed($value);
        $this->attributes['password'] = $hashed ? $value : Hash::make($value);
        $this->pendingReadablePassword = $hashed ? null : $value;
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('password')) {
                // Existing hashes cannot be reversed; capture only explicitly set passwords.
                $user->show_password = in_array($user->role, ['user', 'member'], true)
                    ? $user->pendingReadablePassword : null;
            }
        });
        static::saved(function (User $user) {
            $user->pendingReadablePassword = null;
        });
    }

    public function locations(): HasMany
    {
        return $this->hasMany(UserLocation::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(User::class, 'invited_by_user_id')->where('role', 'member');
    }

    public function locationFixes(): HasMany
    {
        return $this->hasMany(LocationFix::class);
    }

    public function latestFix(): HasOne
    {
        return $this->hasOne(LocationFix::class)->ofMany(['captured_at' => 'max', 'id' => 'max']);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by_user_id');
    }

    public function loginEvents(): HasMany
    {
        return $this->hasMany(LoginEvent::class);
    }
}
