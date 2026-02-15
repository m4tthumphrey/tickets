<?php

namespace Database\Seeders;

use App\Models\AccessCode;
use Illuminate\Database\Seeder;

class AccessCodeSeeder extends Seeder
{
    public function run(): void
    {
        AccessCode::updateOrCreate(
            ['code' => 'DEMO2026'],
            ['label' => 'Demo code', 'expires_after' => 120],
        );

        AccessCode::updateOrCreate(
            ['code' => 'TESTCODE'],
            ['label' => 'Quick-expiry test code', 'expires_after' => 5],
        );
    }
}
