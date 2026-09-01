<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Initial System Administrator
        User::updateOrCreate(
            ['email' => 'admin@atharaman.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@123'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // 2. Demo Tourist Account for testing recommendation flows
        User::updateOrCreate(
            ['email' => 'tourist@atharaman.com'],
            [
                'name' => 'Demo Tourist',
                'password' => Hash::make('Tourist@123'),
                'role' => 'tourist',
                'country' => 'Sri Lanka',
                'native_language' => 'English',
                'preferred_travel_style' => 'adventure',
                'preferred_budget_tier' => 'mid_range',
                'prefers_guided_tours' => false,
                'requires_accessibility' => false,
                'email_verified_at' => now(),
            ]
        );
    }
}
