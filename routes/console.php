<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('gym:open-inventory')->dailyAt('01:00')->timezone('Asia/Jakarta')->withoutOverlapping(30);
