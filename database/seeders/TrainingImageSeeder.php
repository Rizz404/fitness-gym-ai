<?php

namespace Database\Seeders;

use App\Models\Gym;
use Illuminate\Database\Seeder;

class TrainingImageSeeder extends Seeder
{
    public function run(): void
    {
        $gym = Gym::where('slug', 'fitness-gym-ai')->firstOrFail();
        foreach ($gym->trainingAreas as $area) {
            $area->images()->updateOrCreate(['image_url' => 'images/gym/interior.webp'], ['tags' => ['gym', 'studio'], 'alt_text' => $area->name.' — illustrative gym interior', 'sort_order' => 0]);
        }
    }
}
