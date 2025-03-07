<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * @param mixed $authenticatedUser
     * @param \App\Models\User $user
     * @return bool
     */
    public function view(?User $authenticatedUser, User $user): bool
    {
        return $authenticatedUser !== null;
    }
}
