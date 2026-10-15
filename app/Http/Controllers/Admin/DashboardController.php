<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ResolvesCurrentGym;
use App\Http\Controllers\Controller;
use App\Modules\Operations\Queries\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    use ResolvesCurrentGym;

    public function index(Request $request, DashboardMetrics $metrics): View
    {
        $gym = $this->currentGym($request);

        return view('admin.dashboard', [
            'gym' => $gym,
            'stats' => $metrics->counts($gym),
            'recentReservations' => $metrics->recentReservations($gym),
            'openHandovers' => $metrics->latestOpenHandovers($gym),
        ]);
    }
}
