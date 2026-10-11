<?php

namespace App\Policies;

use App\Models\Expense;
use App\Models\User;
use App\Support\EntryWindow;
use Illuminate\Auth\Access\Response;

class ExpensePolicy
{
    public function view(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id;
    }

    public function update(User $user, Expense $expense): Response
    {
        return $this->ownedAndOpen($user, $expense);
    }

    public function delete(User $user, Expense $expense): Response
    {
        return $this->ownedAndOpen($user, $expense);
    }

    public function restore(User $user, Expense $expense): Response
    {
        return $this->ownedAndOpen($user, $expense);
    }

    public function forceDelete(User $user, Expense $expense): Response
    {
        return $expense->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    private function ownedAndOpen(User $user, Expense $expense): Response
    {
        if ($expense->user_id !== $user->id) {
            return Response::denyAsNotFound();
        }

        return EntryWindow::isOpen($expense->date, $user)
            ? Response::allow()
            : Response::deny("That entry's month is closed, so it can't be changed.");
    }
}
