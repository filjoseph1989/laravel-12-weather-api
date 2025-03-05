<?php

namespace App\Services;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserService
{
    /**
     * Return information about the current user
     *
     * @param \App\Models\User $user
     * @return \App\Http\Resources\UserResource
     */
    public function getUser(User $user): UserResource
    {
        $cacheKey = "user_{$user->id}";
        return Cache::remember($cacheKey, 900, function () use ($user) {
            $user->loadMissing('posts');
            return new UserResource($user);
        });
    }
}