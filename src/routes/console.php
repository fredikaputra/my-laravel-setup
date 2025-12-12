<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('model:prune')->monthly();
Schedule::command('queue:prune-batches --hours=168')->monthly();
Schedule::command('queue:prune-failed --hours=168')->monthly();
Schedule::command('passport:purge')->monthly();
Schedule::command('db:monitor --max=20')->everyMinute();
