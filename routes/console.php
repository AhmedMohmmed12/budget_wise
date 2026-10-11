<?php

use App\Models\RecurringEntry;
use App\Models\User;
use App\Services\RecurringPoster;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('budgetwise:post-recurring', function () {
    $posted = 0;

    User::whereIn('id', RecurringEntry::whereNull('paused_at')->select('user_id'))
        ->each(function (User $user) use (&$posted) {
            $posted += RecurringPoster::catchUp($user);
        });

    $this->info("Wrote down {$posted} recurring ".($posted === 1 ? 'entry' : 'entries').'.');
})->purpose('Write down recurring entries that are due today');

// Optional: the app also catches up on each visit, so these only matter
// when a cron entry runs `php artisan schedule:run` every minute.
Schedule::command('budgetwise:post-recurring')->dailyAt('00:15');
Schedule::command('model:prune')->daily();
