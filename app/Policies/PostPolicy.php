<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * @param mixed $user
     * @param \App\Models\Post $post
     * @return bool
     */
    public function view(?User $user, Post $post): bool
    {
        return !$user || $user->id === $post->user_id;
    }

    /**
     * @param mixed $user
     * @param \App\Models\Post $post
     * @return bool
     */
    public function update(?User $user, Post $post): bool
    {
        return $user && $user->id === $post->user_id;
    }

    /**
     * @param mixed $user
     * @param \App\Models\Post $post
     * @return bool
     */
    public function delete(?User $user, Post $post): bool
    {
        return $user && $user->id === $post->user_id;
    }
}