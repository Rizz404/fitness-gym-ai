<?php

namespace Tests\Feature;

use App\Models\Gym;
use App\Models\Reservation;
use App\Models\SessionInventory;
use App\Models\TrainingArea;
use App\Modules\Reservation\Actions\ChangeReservationStatus;
use App\Modules\Reservation\Actions\CreateReservationRequest;
use App\Modules\Reservation\Support\SessionAvailability;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GymBookingLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_booking_keeps_its_program_name_area_name_and_price_after_catalog_changes(): void
    {
        $gym = Gym::factory()->create();
        $area = TrainingArea::factory()->withFee(125000)->create(['gym_id' => $gym->id, 'name' => 'Strength Floor']);
        $area->fitnessProgram->update(['name' => 'Strength Basics']);
        $date = CarbonImmutable::now($gym->timezone)->addDays(2)->startOfDay();
        SessionInventory::factory()->create(['training_area_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '11:00', 'capacity' => 1]);

        $booking = app(CreateReservationRequest::class)->handle($gym, $area->fresh(), ['reservation_date' => $date, 'time_slot' => '11:00', 'participants' => 1, 'guest_name' => 'Ayu']);
        $area->fitnessProgram->update(['name' => 'New program name', 'price' => 200000]);
        $area->update(['name' => 'New studio name']);

        $this->assertDatabaseHas('reservations', ['id' => $booking->id, 'program_name' => 'Strength Basics', 'area_name' => 'Strength Floor', 'price_total' => 125000, 'duration_minutes' => 60, 'status' => 'pending']);
        $this->assertDatabaseHas('session_inventory', ['training_area_id' => $area->id, 'booked_places' => 1]);
    }

    public function test_cancelling_twice_releases_one_place_once_and_reopening_is_refused(): void
    {
        $area = TrainingArea::factory()->create();
        $booking = Reservation::factory()->create(['gym_id' => $area->gym_id, 'training_area_id' => $area->id, 'status' => 'confirmed']);
        $inventory = SessionInventory::factory()->create(['training_area_id' => $area->id, 'reservation_date' => $booking->reservation_date, 'time_slot' => $booking->time_slot, 'capacity' => 3, 'booked_places' => 2]);

        app(ChangeReservationStatus::class)->handle($booking, 'cancelled');
        app(ChangeReservationStatus::class)->handle($booking, 'cancelled');

        $this->assertSame(1, $inventory->fresh()->booked_places);
        $this->assertSame('cancelled', $booking->fresh()->status);
        $this->expectException(ValidationException::class);
        app(ChangeReservationStatus::class)->handle($booking, 'confirmed');
    }

    public function test_inventory_rolls_forward_without_resetting_held_places(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-10-15 09:00', 'Asia/Jakarta'));
        $gym = Gym::factory()->create(['opening_time' => '06:00', 'closing_time' => '22:00']);
        $area = TrainingArea::factory()->create(['gym_id' => $gym->id, 'session_capacity' => 4, 'schedule_times' => ['11:00']]);
        $inventory = SessionInventory::factory()->create(['training_area_id' => $area->id, 'reservation_date' => '2026-10-15', 'time_slot' => '11:00', 'capacity' => 4, 'booked_places' => 2]);

        $this->artisan('gym:open-inventory', ['--gym' => $gym->slug])->assertSuccessful();
        $this->artisan('gym:open-inventory', ['--gym' => $gym->slug])->assertSuccessful();

        $this->assertSame(2, $inventory->fresh()->booked_places);
        $this->assertSame(61, $area->inventory()->count());
        $this->assertDatabaseHas('session_inventory', ['training_area_id' => $area->id, 'reservation_date' => '2026-12-14', 'time_slot' => '11:00']);
    }

    public function test_an_unscheduled_or_paused_program_cannot_be_booked_even_if_inventory_exists(): void
    {
        $area = TrainingArea::factory()->create(['schedule_times' => ['11:00']]);
        $date = CarbonImmutable::now($area->gym->timezone)->addDays(2)->startOfDay();
        SessionInventory::factory()->create(['training_area_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '13:00']);
        SessionInventory::factory()->create(['training_area_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '11:00']);

        $this->assertNull(app(SessionAvailability::class)->quote($area, $date, '13:00'));
        $area->fitnessProgram->update(['is_sold_out' => true]);
        $this->assertNull(app(SessionAvailability::class)->quote($area->fresh(), $date, '11:00'));
    }

    public function test_the_action_rejects_cross_gym_areas_and_multiple_participants_without_holding_places(): void
    {
        $area = TrainingArea::factory()->create();
        $otherGym = Gym::factory()->create();
        $date = CarbonImmutable::now($area->gym->timezone)->addDays(2)->startOfDay();
        $inventory = SessionInventory::factory()->create(['training_area_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '11:00']);
        $data = ['reservation_date' => $date, 'time_slot' => '11:00', 'participants' => 1, 'guest_name' => 'Ayu'];

        $this->assertNull(app(CreateReservationRequest::class)->handle($otherGym, $area, $data));
        $this->assertNull(app(CreateReservationRequest::class)->handle($area->gym, $area, [...$data, 'participants' => 2]));

        $this->assertSame(0, Reservation::count());
        $this->assertSame(0, $inventory->fresh()->booked_places);
    }
}
