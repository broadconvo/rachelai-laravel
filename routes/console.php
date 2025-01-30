<?php

use App\Console\Commands\GenerateDraftCommand;
use App\Console\Commands\GenerateFaqCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command(GenerateDraftCommand::class)
    ->everyTwoMinutes()
    ->withoutOverlapping(300); // Lock expires after 5 minutes

Schedule::command(GenerateFaqCommand::class)
    ->everyFiveMinutes()
    //->daily()
    ->withoutOverlapping(300); // Lock expires after 5 minutes
