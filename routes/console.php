<?php

use App\Console\Commands\ProcessGmailMessages;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(ProcessGmailMessages::class)
    ->everyMinute()
    ->withoutOverlapping(300); // Lock expires after 5 minutes
