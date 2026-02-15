<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('tc:sync-teams')->daily();
Schedule::command('tc:sync-venues')->daily();
Schedule::command('tc:sync-competitions')->daily();
Schedule::command('tc:sync-ticket-categories')->daily();
Schedule::command('tc:sync-products')->everyFifteenMinutes();
