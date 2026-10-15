<?php

namespace Tests\Unit;

use App\Models\TrainingArea;
use App\Modules\Reservation\Support\SessionAvailability;
use Tests\TestCase;

class SessionAvailabilityTest extends TestCase
{
    public function test_a_slot_is_a_sixty_minute_range(): void
    {
        $this->assertSame('11:00–12:00', SessionAvailability::slotRange('11:00'));
        $this->assertSame('21:00–22:00', SessionAvailability::slotRange('21:00'));
    }

    public function test_a_party_must_fit_the_area_capacity(): void
    {
        $area = new TrainingArea(['min_participants' => 1, 'max_participants' => 1]);
        $availability = new SessionAvailability;

        $this->assertFalse($availability->fitsParty($area, 0));
        $this->assertTrue($availability->fitsParty($area, 1));
        $this->assertFalse($availability->fitsParty($area, 2));
        $this->assertFalse($availability->fitsParty($area, 9));
    }
}
