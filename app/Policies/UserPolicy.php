<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function view(?User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser !== null;
    }
}
