<?php

use App\Console\Commands\GenerateDraftCommand;
use App\Console\Commands\GenerateFaqCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command(GenerateDraftCommand::class)
    ->everyTwoMinutes()
    ->withoutOverlapping(300); // Lock expires after 5 minutes

Schedule::command(GenerateFaqCommand::class)
    ->everyFiveMinutes()
    //->daily()
    ->withoutOverlapping(300); // Lock expires after 5 minutes
