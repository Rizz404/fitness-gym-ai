<?php

namespace Tests\Feature;

use App\Models\FitnessProgram;
use App\Models\Gym;
use App\Models\SessionInventory;
use App\Models\TrainingArea;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GymStageTest extends TestCase
{
    use RefreshDatabase;

    private Gym $gym;

    private FitnessProgram $strength;

    private TrainingArea $terrace;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->gym = Gym::create([
            'name' => 'Demo Gym', 'slug' => 'demo', 'public_status' => 'published', 'city' => 'Jakarta', 'country' => 'Indonesia', 'currency' => 'IDR',
            'default_locale' => 'id', 'opening_time' => '08:00', 'closing_time' => '22:00', 'phone' => '+62 21 1234', 'whatsapp' => '6281234567890',
            'description' => 'Gym demo di Kemang.',
        ]);

        $this->strength = $this->gym->fitnessPrograms()->create([
            'name' => 'Strength Basics', 'slug' => 'strength-basics', 'category' => 'strength', 'price' => 32000, 'description' => 'Latihan kekuatan terpandu.',
            'translations' => ['en' => ['name' => 'Strength Basics', 'description' => 'Guided strength training.']], 'tags' => ['beginner'], 'requirements' => ['sports_shoes'], 'difficulty' => 'beginner',
            'image_url' => 'images/program/strength-basics.svg', 'is_active' => true, 'sort_order' => 0,
        ]);
        $this->gym->fitnessPrograms()->create(['name' => 'Mobility Reset', 'slug' => 'mobility-reset', 'category' => 'recovery', 'price' => 40000, 'is_sold_out' => true, 'is_active' => true, 'sort_order' => 1]);
        $this->gym->fitnessPrograms()->create(['name' => 'Retired Program', 'slug' => 'retired', 'category' => 'strength', 'price' => 1000, 'is_active' => false]);

        $this->terrace = $this->createTrainingArea($this->gym, [
            'name' => 'Functional Studio', 'slug' => 'functional-studio', 'area_type' => 'outdoor', 'min_participants' => 1, 'max_participants' => 6, 'features' => ['trainer'],
            'fitness_program_id' => $this->strength->id, 'is_active' => true,
        ]);
        $this->terrace->images()->create(['image_url' => 'images/training/functional-studio.svg', 'alt_text' => 'Terrace', 'sort_order' => 0]);
        SessionInventory::create([
            'training_area_id' => $this->terrace->id, 'reservation_date' => CarbonImmutable::now('Asia/Jakarta')->addDays(3)->toDateString(), 'time_slot' => '13:00',
            'capacity' => 2, 'booked_places' => 0,
        ]);

        $this->gym->knowledgeItems()->create(['category' => 'facilities', 'title' => 'Wi-Fi gratis', 'body' => 'Wi-Fi cepat untuk semua tamu.', 'tags' => ['wifi'], 'is_active' => true]);
        $this->gym->knowledgeItems()->create(['category' => 'policies', 'title' => 'Kebijakan reservasi', 'body' => 'Batal gratis 2 jam sebelumnya.', 'is_active' => true]);
        $this->gym->knowledgeItems()->create(['category' => 'faq', 'title' => 'Ada musala?', 'body' => 'Ada, di sebelah toilet.', 'is_active' => true]);
    }

    public function test_the_opening_screen_lists_published_gyms(): void
    {
        Gym::create(['name' => 'Draft Gym', 'slug' => 'draft']);

        $this->get('/')
            ->assertOk()
            ->assertSee('Demo Gym')
            ->assertDontSee('Draft Gym')
            ->assertSee(route('gym.program', ['gymSlug' => 'demo', 'lang' => 'id']), false);
    }

    public function test_the_counter_greets_participants_in_the_requested_language(): void
    {
        $this->get('/demo')->assertOk()->assertSee('Demo Gym')->assertSee('Langkah kecil.');
        $this->get('/demo?lang=en')->assertOk()->assertSee('Small steps.')->assertSee('lang="en"', false);
        $this->get('/demo?lang=ja')->assertOk()->assertSee('小さな一歩。');
        $this->get('/demo?lang=xx')->assertOk()->assertSee('Langkah kecil.');
    }

    public function test_every_scene_renders_and_carries_the_concierge_chat(): void
    {
        $routes = [
            'gym.show', 'gym.program', 'gym.training', 'gym.facilities', 'gym.info', 'gym.staff', 'gym.reservation',
        ];

        foreach ($routes as $route) {
            $this->get(route($route, ['gymSlug' => 'demo']))
                ->assertOk()
                ->assertSee('id="concierge-app"', false)
                ->assertSee('data-scene=', false);
        }
    }

    public function test_unpublished_and_unknown_gyms_are_not_found(): void
    {
        Gym::create(['name' => 'Draft', 'slug' => 'draft']);

        $this->get('/draft')->assertNotFound();
        $this->get('/draft/program')->assertNotFound();
        $this->get('/nowhere')->assertNotFound();
    }

    public function test_the_program_scene_narrates_from_stored_data_and_lists_active_items(): void
    {
        $this->get('/demo/program?lang=en')
            ->assertOk()
            ->assertSee('Explore 2 training programs, from IDR 32.000')
            ->assertSee('Strength Basics')
            ->assertSee('Mobility Reset')
            ->assertDontSee('Retired Program');
    }

    public function test_a_fitness_program_shows_its_price_requirements_and_tags(): void
    {
        $this->get('/demo/program/strength-basics?lang=en')
            ->assertOk()
            ->assertSee('Strength Basics')
            ->assertSee('IDR 32.000')
            ->assertSee('Guided strength training.')
            ->assertSee('Sports shoes')
            ->assertSee('Beginner')
            ->assertSee('Preparation: Sports shoes.')
            ->assertSee('images/program/strength-basics.svg', false);

        $this->get('/demo/program/mobility-reset?lang=en')->assertOk()->assertSee('Temporarily unavailable');

        $this->get('/demo/program/retired')->assertNotFound();
        $this->get('/demo/program/nothing-here')->assertNotFound();
    }

    public function test_the_training_scenes_show_capacity_and_fees(): void
    {
        $this->get('/demo/training?lang=en')->assertOk()->assertSee('Functional Studio')->assertSee('Explore Functional Studio and its training program.');

        $this->get('/demo/training/functional-studio?lang=en')
            ->assertOk()
            ->assertSee('Functional Studio')
            ->assertSee('Instructor')
            ->assertSee('Session price: IDR 32.000.')
            ->assertSee('area=functional-studio', false);

        $this->get('/demo/training/nowhere')->assertNotFound();
    }

    public function test_the_facility_scenes_show_stored_services(): void
    {
        $facility = $this->gym->knowledgeItems()->where('category', 'facilities')->firstOrFail();

        $this->get('/demo/facilities')->assertOk()->assertSee('Wi-Fi gratis')->assertDontSee('Kebijakan reservasi');
        $this->get('/demo/facilities/'.$facility->id)->assertOk()->assertSee('Wi-Fi cepat untuk semua tamu.');
        $this->get('/demo/facilities/99999')->assertNotFound();
    }

    public function test_the_info_scene_shows_opening_hours_policies_and_faq(): void
    {
        $this->get('/demo/info?lang=en')
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('22:00')
            ->assertSee('We are open from 08:00 to 22:00.')
            ->assertSee('Kebijakan reservasi')
            ->assertSee('Ada musala?')
            ->assertSee('Gym demo di Kemang.');
    }

    public function test_the_staff_scene_offers_whatsapp_and_phone(): void
    {
        $this->get('/demo/staff')
            ->assertOk()
            ->assertSee('https://wa.me/6281234567890', false)
            ->assertSee('tel:+62211234', false);
    }

    public function test_the_reservation_scene_offers_slots_and_training_areas(): void
    {
        $this->get('/demo/reservation?area=functional-studio&lang=en')
            ->assertOk()
            ->assertSee('data-wizard', false)
            ->assertSee('data-preselect-area="functional-studio"', false)
            ->assertSee('13:00–14:00')
            ->assertSee('Functional Studio');
    }
}
