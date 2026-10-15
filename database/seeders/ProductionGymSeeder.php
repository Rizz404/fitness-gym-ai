<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;

class ProductionGymSeeder extends Seeder
{
    public function run(): void
    {
        Gym::firstOrCreate(['slug' => 'fitness-gym-ai'], ['name' => 'Fitness Gym AI', 'timezone' => 'Asia/Jakarta', 'currency' => 'IDR', 'default_locale' => 'id', 'opening_time' => '06:00', 'closing_time' => '22:00', 'public_status' => 'published']);
    }
}
