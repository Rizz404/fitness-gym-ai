<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class SessionInventorySeeder extends Seeder
{
    public function run(): void
    {
        Artisan::call('gym:open-inventory');
    }
}
