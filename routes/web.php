<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FitnessProgramController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\KnowledgeItemController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\TrainingAreaController;
use App\Http\Controllers\Api\ConciergeChatController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\GymPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GymPageController::class, 'index'])->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('program-items', FitnessProgramController::class)->except('show');
        Route::resource('training-areas', TrainingAreaController::class)->except('show');
        Route::resource('knowledge-items', KnowledgeItemController::class)->except('show');

        Route::get('reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
        Route::patch('reservations/{reservation}/status', [AdminReservationController::class, 'updateStatus'])->name('reservations.status');

        Route::get('handovers', [HandoverController::class, 'index'])->name('handovers.index');
        Route::get('handovers/{handover}', [HandoverController::class, 'show'])->name('handovers.show');
        Route::post('handovers/{handover}/reply', [HandoverController::class, 'reply'])->name('handovers.reply');
        Route::post('handovers/{handover}/resolve', [HandoverController::class, 'resolve'])->name('handovers.resolve');
    });
});

Route::prefix('{gymSlug}')->middleware('gym')->group(function () {
    Route::get('/', [GymPageController::class, 'show'])->name('gym.show');
    Route::get('program', [GymPageController::class, 'program'])->name('gym.program');
    Route::get('program/{itemSlug}', [GymPageController::class, 'fitnessProgram'])->name('gym.program-item');
    Route::get('training', [GymPageController::class, 'training'])->name('gym.training');
    Route::get('training/{areaSlug}', [GymPageController::class, 'trainingArea'])->name('gym.training-area');
    Route::get('facilities', [GymPageController::class, 'facilities'])->name('gym.facilities');
    Route::get('facilities/{facilityId}', [GymPageController::class, 'facility'])->whereNumber('facilityId')->name('gym.facility');
    Route::get('info', [GymPageController::class, 'info'])->name('gym.info');
    Route::get('staff', [GymPageController::class, 'staff'])->name('gym.staff');
    Route::get('reservation', [GymPageController::class, 'reservationScene'])->name('gym.reservation');

    Route::prefix('reservation')->name('reservation.')->middleware('throttle:20,1')->group(function () {
        Route::post('quote', [ReservationController::class, 'quote'])->name('quote');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
    });

    Route::prefix('concierge')->name('concierge.')->group(function () {
        Route::post('start', [ConciergeChatController::class, 'start'])->middleware('throttle:concierge-start')->name('start');
        Route::post('message', [ConciergeChatController::class, 'message'])->middleware('throttle:concierge-message')->name('message');
        Route::get('history', [ConciergeChatController::class, 'history'])->middleware('throttle:concierge-history')->name('history');
        Route::post('speech', [ConciergeChatController::class, 'speech'])->middleware('throttle:concierge-speech')->name('speech');
    });
});
