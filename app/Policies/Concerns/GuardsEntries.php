<?php

namespace App\Policies\Concerns;

use App\Models\User;
use App\Support\EntryWindow;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Model;

/**
 * Dated entries (expenses, income, goal contributions) belong to one user and
 * can only change while their month is open.
 */
trait GuardsEntries
{
    public function update(User $user, Model $entry): Response
    {
        return $this->ownedAndOpen($user, $entry);
    }

    public function delete(User $user, Model $entry): Response
    {
        return $this->ownedAndOpen($user, $entry);
    }

    public function restore(User $user, Model $entry): Response
    {
        return $this->ownedAndOpen($user, $entry);
    }

    public function forceDelete(User $user, Model $entry): Response
    {
        return $entry->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    private function ownedAndOpen(User $user, Model $entry): Response
    {
        if ($entry->user_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        return EntryWindow::isOpen($entry->date, $user)
            ? Response::allow()
            : Response::deny("That entry's month is closed, so it can't be changed.");
    }
}
