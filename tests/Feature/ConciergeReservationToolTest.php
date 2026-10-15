<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\Gym;
use App\Models\Reservation;
use App\Models\SessionInventory;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Support\RunsConciergeTools;
use Tests\TestCase;

class ConciergeReservationToolTest extends TestCase
{
    use RefreshDatabase, RunsConciergeTools;

    public function test_ai_reservation_tool_creates_a_reference_and_holds_one_table(): void
    {
        $gym = Gym::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $area = $this->createTrainingArea($gym, ['name' => 'Private Room', 'slug' => 'private-room', 'area_type' => 'private', 'min_participants' => 1, 'max_participants' => 1, 'session_price' => 150000, 'is_active' => true]);
        $conversation = Conversation::create(['gym_id' => $gym->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $date = CarbonImmutable::now($gym->timezone)->addDays(5);
        foreach (['11:00', '13:00'] as $slot) {
            SessionInventory::create(['training_area_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => $slot, 'capacity' => 1, 'booked_places' => 0]);
        }

        $input = [
            'training_area_slug' => 'private-room', 'date' => $date->toDateString(), 'time_slot' => '11:00',
            'participants' => 1, 'guest_name' => 'Ayu', 'guest_phone' => '+62 811 111 222',
        ];

        $quote = $this->runTool($gym, $conversation, 'check_session_availability', $input);
        $this->assertTrue($quote['ui']['available']);
        $this->assertSame(150000.0, $quote['ui']['quote']['session_price']);

        $result = $this->runTool($gym, $conversation, 'create_reservation_request', $input);

        $reservation = Reservation::firstOrFail();
        $this->assertSame($reservation->reference, $result['ui']['reservation']['reservation_reference']);
        $this->assertSame(1, $reservation->participants);
        $this->assertSame('11:00', $reservation->time_slot);
        $this->assertSame(150000.0, (float) $reservation->price_total);
        $this->assertSame([1, 0], SessionInventory::orderBy('time_slot')->pluck('booked_places')->all());

        $repeated = $this->runTool($gym, $conversation, 'create_reservation_request', $input);
        $this->assertSame($reservation->reference, $repeated['ui']['reservation']['reservation_reference']);
        $this->assertSame(1, Reservation::count());

        $taken = $this->runTool($gym, $conversation, 'create_reservation_request', [...$input, 'guest_name' => 'Budi']);
        $this->assertNull($taken['ui']);
        $this->assertSame(1, Reservation::count());

        $full = $this->runTool($gym, $conversation, 'check_session_availability', $input);
        $this->assertFalse($full['ui']['available']);
        $this->assertStringContainsString('13:00', $full['text']);
    }

    public function test_the_tools_refuse_a_party_that_does_not_fit_and_a_slot_that_does_not_exist(): void
    {
        $gym = Gym::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $this->createTrainingArea($gym, ['name' => 'Bar', 'slug' => 'bar', 'area_type' => 'functional', 'min_participants' => 1, 'max_participants' => 2, 'is_active' => true]);
        $conversation = Conversation::create(['gym_id' => $gym->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($gym->timezone)->addDays(2);
        SessionInventory::create(['training_area_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '15:00', 'capacity' => 2, 'booked_places' => 0]);

        $base = ['training_area_slug' => 'bar', 'date' => $date->toDateString(), 'guest_name' => 'Ayu', 'guest_phone' => '+62 811 111 222'];

        $tooMany = $this->runTool($gym, $conversation, 'create_reservation_request', [...$base, 'time_slot' => '15:00', 'participants' => 5]);
        $this->assertNull($tooMany['ui']);

        $unknownSlot = $this->runTool($gym, $conversation, 'check_session_availability', [...$base, 'time_slot' => '03:00', 'participants' => 1]);
        $this->assertStringContainsString('15:00', $unknownSlot['text']);

        $past = $this->runTool($gym, $conversation, 'check_session_availability', [...$base, 'date' => '2001-01-01', 'time_slot' => '15:00', 'participants' => 1]);
        $this->assertStringContainsString('past', $past['text']);

        $this->assertSame(0, Reservation::count());
    }

    public function test_the_reservation_tool_needs_a_usable_name_and_contact_before_it_books(): void
    {
        $gym = Gym::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $area = $this->createTrainingArea($gym, ['name' => 'Bar', 'slug' => 'bar', 'area_type' => 'functional', 'min_participants' => 1, 'max_participants' => 4, 'is_active' => true]);
        $conversation = Conversation::create(['gym_id' => $gym->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);
        $date = CarbonImmutable::now($gym->timezone)->addDays(2);
        SessionInventory::create(['training_area_id' => $area->id, 'reservation_date' => $date->toDateString(), 'time_slot' => '15:00', 'capacity' => 2, 'booked_places' => 0]);

        $base = ['training_area_slug' => 'bar', 'date' => $date->toDateString(), 'time_slot' => '15:00', 'participants' => 1];

        foreach ([
            ['guest_phone' => '+62 811 111 222'],
            ['guest_name' => 'Ayu'],
            ['guest_name' => 'Ayu', 'guest_phone' => 'call me'],
            ['guest_name' => 'Ayu', 'guest_email' => 'not-an-email'],
        ] as $guest) {
            $this->assertNull($this->runTool($gym, $conversation, 'create_reservation_request', [...$base, ...$guest])['ui']);
        }

        $this->assertSame(0, Reservation::count());

        $byEmail = $this->runTool($gym, $conversation, 'create_reservation_request', [...$base, 'guest_name' => 'Ayu', 'guest_email' => 'ayu@example.com']);

        $this->assertNotNull($byEmail['ui']);
        $this->assertSame('email', Reservation::firstOrFail()->contact_type);
    }

    public function test_program_search_filters_by_diet_and_budget_and_hides_unpublished_items(): void
    {
        $gym = Gym::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published', 'currency' => 'IDR']);
        $gym->fitnessPrograms()->create(['name' => 'Aglio Olio', 'slug' => 'aglio-olio', 'category' => 'personal_training', 'price' => 48000, 'tags' => ['beginner'], 'is_active' => true]);
        $gym->fitnessPrograms()->create(['name' => 'Club Sandwich', 'slug' => 'club-sandwich', 'category' => 'personal_training', 'price' => 52000, 'tags' => ['intermediate'], 'is_active' => true]);
        $gym->fitnessPrograms()->create(['name' => 'Secret Dish', 'slug' => 'secret', 'category' => 'personal_training', 'price' => 10000, 'tags' => ['beginner'], 'is_active' => false]);
        $conversation = Conversation::create(['gym_id' => $gym->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        $vegan = $this->runTool($gym, $conversation, 'search_program', ['level' => 'beginner']);
        $this->assertSame(['aglio-olio'], collect($vegan['ui']['items'])->pluck('fitness_program_slug')->all());

        $cheap = $this->runTool($gym, $conversation, 'search_program', ['category' => 'personal_training', 'max_price' => 50000]);
        $this->assertSame(['aglio-olio'], collect($cheap['ui']['items'])->pluck('fitness_program_slug')->all());

        $none = $this->runTool($gym, $conversation, 'search_program', ['query' => 'sushi']);
        $this->assertNull($none['ui']);
    }
}
