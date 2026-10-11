<?php

namespace App\Policies;

use App\Models\SavingsGoal;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SavingsGoalPolicy
{
    public function update(User $user, SavingsGoal $goal): Response
    {
        return $goal->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    public function delete(User $user, SavingsGoal $goal): Response
    {
        return $this->update($user, $goal);
    }
}
