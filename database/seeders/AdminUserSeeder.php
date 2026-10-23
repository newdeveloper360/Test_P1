<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('IP_TRACKER_ADMIN_PASSWORD', '112233');
        if (app()->environment('production') && $password === '112233') {
            throw new \RuntimeException('Set a strong IP_TRACKER_ADMIN_PASSWORD for production.');
        }
        User::firstOrCreate(
            ['phone' => env('IP_TRACKER_ADMIN_PHONE', '1234567890')],
            [
                'name' => 'Administrator',
                'password' => $password,
                'role' => 'admin',
                'is_blocked' => false,
            ]
        );
    }
}
