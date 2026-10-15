<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Models\HandoverRequest;
use App\Models\Reservation;
use App\Models\SessionInventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    private Gym $gym;

    private Gym $otherGym;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->gym = Gym::create(['name' => 'Demo', 'slug' => 'demo', 'public_status' => 'published']);
        $this->otherGym = Gym::create(['name' => 'Other', 'slug' => 'other', 'public_status' => 'published']);

        $this->staff = User::factory()->create();
        $this->gym->users()->attach($this->staff->id, ['role' => 'owner', 'status' => 'active']);
    }

    private function foreignReservation(): Reservation
    {
        $area = $this->createTrainingArea($this->otherGym, ['name' => 'Foreign', 'slug' => 'foreign', 'area_type' => 'indoor', 'is_active' => true]);

        return Reservation::factory()->create([
            'reference' => 'GY-FOREIGN', 'gym_id' => $this->otherGym->id, 'training_area_id' => $area->id, 'guest_name' => 'Foreign Guest',
            'reservation_date' => now()->addDays(3)->toDateString(), 'time_slot' => '11:00', 'participants' => 1,
        ]);
    }

    private function foreignHandover(): HandoverRequest
    {
        $conversation = Conversation::create(['gym_id' => $this->otherGym->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en']);

        return HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'complaint', 'summary' => 'Foreign complaint']);
    }

    public function test_visitors_are_sent_to_the_login_page_for_every_admin_screen(): void
    {
        foreach (['admin.dashboard', 'admin.program-items.index', 'admin.training-areas.index', 'admin.knowledge-items.index', 'admin.reservations.index', 'admin.handovers.index'] as $route) {
            $this->get(route($route))->assertRedirect(route('admin.login'));
        }
    }

    public function test_staff_only_see_reservations_of_their_own_gym(): void
    {
        $area = $this->createTrainingArea($this->gym, ['name' => 'Lounge', 'slug' => 'lounge', 'area_type' => 'indoor', 'is_active' => true]);
        Reservation::factory()->create([
            'reference' => 'GY-MINE', 'gym_id' => $this->gym->id, 'training_area_id' => $area->id, 'guest_name' => 'My Guest',
            'reservation_date' => now()->addDays(3)->toDateString(), 'time_slot' => '13:00', 'participants' => 1,
        ]);
        $this->foreignReservation();

        $this->actingAs($this->staff)->get(route('admin.reservations.index'))
            ->assertOk()
            ->assertSee('GY-MINE')
            ->assertDontSee('GY-FOREIGN');
    }

    public function test_staff_cannot_change_another_gyms_reservation(): void
    {
        $reservation = $this->foreignReservation();

        $this->actingAs($this->staff)
            ->patch(route('admin.reservations.status', $reservation), ['status' => 'confirmed'])
            ->assertNotFound();

        $this->assertSame(Reservation::STATUS_PENDING, $reservation->fresh()->status);
    }

    public function test_cancelling_a_reservation_releases_its_table(): void
    {
        $area = $this->createTrainingArea($this->gym, ['name' => 'Lounge', 'slug' => 'lounge', 'area_type' => 'indoor', 'is_active' => true]);
        $date = now()->addDays(3)->toDateString();
        SessionInventory::create(['training_area_id' => $area->id, 'reservation_date' => $date, 'time_slot' => '13:00', 'capacity' => 2, 'booked_places' => 1]);
        $reservation = Reservation::factory()->create([
            'reference' => 'GY-MINE', 'gym_id' => $this->gym->id, 'training_area_id' => $area->id, 'guest_name' => 'My Guest',
            'reservation_date' => $date, 'time_slot' => '13:00', 'participants' => 1,
        ]);

        $this->actingAs($this->staff)
            ->patch(route('admin.reservations.status', $reservation), ['status' => 'cancelled'])
            ->assertRedirect();

        $this->assertSame(Reservation::STATUS_CANCELLED, $reservation->fresh()->status);
        $this->assertSame(0, SessionInventory::firstOrFail()->booked_places);
    }

    public function test_staff_cannot_open_or_answer_another_gyms_handover(): void
    {
        $handover = $this->foreignHandover();

        $this->actingAs($this->staff)->get(route('admin.handovers.show', $handover))->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Hi'])->assertNotFound();
        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertNotFound();
    }

    public function test_resolving_a_handover_hands_the_conversation_back_to_the_concierge(): void
    {
        $conversation = Conversation::create(['gym_id' => $this->gym->id, 'guest_token' => (string) Str::uuid(), 'locale' => 'en', 'status' => Conversation::STATUS_HANDED_OVER]);
        $handover = HandoverRequest::create(['conversation_id' => $conversation->id, 'reason' => 'private_event', 'summary' => 'Birthday for 20']);

        $this->actingAs($this->staff)->post(route('admin.handovers.reply', $handover), ['message' => 'Happy to help!'])->assertRedirect();
        $this->assertSame('Happy to help!', ConversationMessage::where('role', ConversationMessage::ROLE_STAFF)->firstOrFail()->content);

        $this->actingAs($this->staff)->post(route('admin.handovers.resolve', $handover))->assertRedirect(route('admin.handovers.index'));

        $this->assertSame(HandoverRequest::STATUS_RESOLVED, $handover->fresh()->status);
        $this->assertSame(Conversation::STATUS_ACTIVE, $conversation->fresh()->status);
    }

    public function test_staff_manage_the_program_of_their_own_gym_only(): void
    {
        $this->actingAs($this->staff)->post(route('admin.program-items.store'), [
            'category' => 'strength', 'name' => 'Flat White', 'price' => 34000, 'duration_minutes' => 60, 'tags' => 'Halal, Vegetarian', 'requirements' => 'milk', 'is_active' => '1',
        ])->assertRedirect(route('admin.program-items.index'));

        $item = $this->gym->fitnessPrograms()->firstOrFail();
        $this->assertSame('flat-white', $item->slug);
        $this->assertSame(['halal', 'vegetarian'], $item->tags);
        $this->assertTrue($item->is_active);

        $foreign = $this->otherGym->fitnessPrograms()->create(['category' => 'strength', 'name' => 'Foreign', 'slug' => 'foreign', 'price' => 1]);

        $this->actingAs($this->staff)->get(route('admin.program-items.edit', $foreign))->assertNotFound();
        $this->actingAs($this->staff)->delete(route('admin.program-items.destroy', $foreign))->assertNotFound();
        $this->assertNotNull($foreign->fresh());
    }

    public function test_training_area_capacity_is_validated(): void
    {
        $this->actingAs($this->staff)->post(route('admin.training-areas.store'), [
            'fitness_program_id' => FitnessProgram::factory()->create(['gym_id' => $this->gym->id])->id, 'session_capacity' => 12, 'schedule_times' => '11:00, 13:00', 'name' => 'Patio', 'area_type' => 'outdoor', 'min_participants' => 6, 'max_participants' => 2, 'session_price' => 0,
        ])->assertSessionHasErrors('max_participants');

        $this->actingAs($this->staff)->post(route('admin.training-areas.store'), [
            'fitness_program_id' => FitnessProgram::factory()->create(['gym_id' => $this->gym->id])->id, 'session_capacity' => 12, 'schedule_times' => '11:00, 13:00', 'name' => 'Patio', 'area_type' => 'outdoor', 'min_participants' => 1, 'max_participants' => 1, 'session_price' => 0, 'features' => 'Pet Friendly, wifi', 'is_active' => '1',
        ])->assertRedirect(route('admin.training-areas.index'));

        $this->assertSame(['pet_friendly', 'wifi'], $this->gym->trainingAreas()->firstOrFail()->features);
    }

    public function test_the_dashboard_counts_only_the_staffs_own_gym(): void
    {
        $this->foreignHandover();
        $this->foreignReservation();

        $this->actingAs($this->staff)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('stats', fn (array $stats) => $stats['open_handovers'] === 0 && $stats['pending_reservations'] === 0);
    }

    public function test_an_account_without_a_gym_is_turned_away(): void
    {
        $this->actingAs(User::factory()->create())->get(route('admin.dashboard'))->assertForbidden();
    }
}
