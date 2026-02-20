<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tc:sync-teams')->hourlyAt('00:00');
Schedule::command('tc:sync-venues')->daily();
Schedule::command('tc:sync-competitions')->daily();
Schedule::command('tc:sync-ticket-categories')->everyFifteenMinutes();
Schedule::command('tc:sync-products')->everyFifteenMinutes();
