<?php

namespace App\Services;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

class PostService
{
    /**
     * Return paginated posts
     *
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedPosts(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Post::with('user')->paginate($perPage);
    }

    /**
     * Return single post
     *
     * @param \App\Models\Post $post
     * @return \App\Models\Post
     */
    public function getPost(Post $post)
    {
        $cacheKey = "post_{$post->id}";
        return Cache::remember($cacheKey, 900, function () use ($post) {
            $post->loadMissing('user');
            return $post;
        });
    }

    /**
     * Create posts
     *
     * @param array $data
     * @param int $userId
     * @return Post
     */
    public function createPost(array $data, int $userId): Post
    {
        $post = Post::create([
            'title' => $data['title'],
            'content' => $data['content'],
            'user_id' => $userId,
        ]);
        $post->loadMissing('user');
        return $post;
    }

    /**
     * Update posts
     *
     * @param \App\Models\Post $post
     * @param array $data
     * @return Post
     */
    public function updatePost(Post $post, array $data): Post
    {
        // $post->update([
        //     'title' => $data['title'],
        //     'content' => $data['content'],
        // ]);
        // $post->loadMissing('user');
        // $post->refresh();
        // lets update post
        $post->update([
            'title' => $data['title'],
            'content' => $data['content']
        ]);

        // $post = Post::where('id', $id)->first();
        $post->loadMissing('user');
        $post->refresh();
        return $post;
    }

    /**
     * Delete posts
     *
     * @param \App\Models\Post $post
     * @return void
     */
    public function deletePost(Post $post): void
    {
        $post->delete();
        Cache::forget("post_{$post->id}");
    }
}