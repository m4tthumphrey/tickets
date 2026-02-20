<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tc:sync-teams')->hourly();
Schedule::command('tc:sync-venues')->daily();
Schedule::command('tc:sync-competitions')->daily();
Schedule::command('tc:sync-ticket-categories')->everyFifteenMinutes();
Schedule::command('tc:sync-products')->everyFifteenMinutes();
