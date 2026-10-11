<?php

namespace App\Policies;

use App\Models\RecurringEntry;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class RecurringEntryPolicy
{
    public function update(User $user, RecurringEntry $entry): Response
    {
        return $entry->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    public function delete(User $user, RecurringEntry $entry): Response
    {
        return $this->update($user, $entry);
    }
}
