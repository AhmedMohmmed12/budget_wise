<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CategoryPolicy
{
    public function update(User $user, Category $category): Response
    {
        return $category->user_id === $user->id ? Response::allow() : Response::denyAsNotFound();
    }

    public function delete(User $user, Category $category): Response
    {
        return $this->update($user, $category);
    }
}
