<?php

namespace App\Console\Commands;

use App\Models\Gym;
use App\Models\SessionInventory;
use App\Modules\Reservation\Support\SessionAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class OpenInventoryCommand extends Command
{
    protected $signature = 'gym:open-inventory {--gym= : Limit to one gym slug}';

    protected $description = 'Open the next 60 days of training sessions without changing held places';

    public function handle(): int
    {
        foreach (Gym::query()->when($this->option('gym'), fn ($q) => $q->where('slug', $this->option('gym')))->with('trainingAreas.fitnessProgram')->lazyById() as $gym) {
            $today = CarbonImmutable::now($gym->timezone)->startOfDay();
            foreach ($gym->trainingAreas as $area) {
                if (! $area->is_active || ! $area->fitnessProgram?->is_active) {
                    continue;
                }
                $rows = [];
                for ($day = 0; $day <= SessionAvailability::MAX_ADVANCE_DAYS; $day++) {
                    foreach ($area->schedule_times ?? [] as $slot) {
                        if ($slot < substr($gym->opening_time, 0, 5) || $slot === '23:00') {
                            continue;
                        }
                        $end = CarbonImmutable::parse($slot)->addHour()->format('H:i');
                        if ($end > substr($gym->closing_time, 0, 5)) {
                            continue;
                        }
                        $rows[] = ['training_area_id' => $area->id, 'reservation_date' => $today->addDays($day)->toDateString(), 'time_slot' => $slot, 'capacity' => $area->session_capacity, 'booked_places' => 0, 'created_at' => now(), 'updated_at' => now()];
                    }
                }
                foreach (array_chunk($rows, 100) as $chunk) {
                    SessionInventory::query()->upsert($chunk, ['training_area_id', 'reservation_date', 'time_slot'], ['capacity', 'updated_at']);
                }
            }
        }
        $this->info('Session inventory ready. Existing bookings preserved.');

        return self::SUCCESS;
    }
}
