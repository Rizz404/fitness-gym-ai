<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentGym;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReservationStatusRequest;
use App\Models\Reservation;
use App\Modules\Reservation\Actions\ChangeReservationStatus;
use App\Modules\Reservation\Enums\ReservationStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReservationController extends Controller
{
    use ResolvesCurrentGym;

    public function index(Request $request): View
    {
        $gym = $this->currentGym($request);

        $status = in_array($request->query('status'), ReservationStatus::values(), true)
            ? $request->query('status')
            : null;

        $reservations = $gym->reservations()
            ->with('trainingArea')
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reservations.index', compact('gym', 'reservations', 'status'));
    }

    public function updateStatus(UpdateReservationStatusRequest $request, Reservation $reservation, ChangeReservationStatus $changeStatus): RedirectResponse
    {
        $this->currentGym($request);

        $status = $request->validated('status');

        $changeStatus->handle($reservation, $status);

        return back()->with('status', "Reservation {$reservation->reference} marked as {$status}.");
    }
}
