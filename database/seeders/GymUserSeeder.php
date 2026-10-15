<?php

namespace Database\Seeders;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Database\Seeder;

class GymUserSeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::where('name', GymSeeder::NAME)->firstOrFail();
        $owner = User::where('email', UserSeeder::OWNER_EMAIL)->firstOrFail();

        $gym->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner', 'status' => 'active'],
        ]);
    }
}
