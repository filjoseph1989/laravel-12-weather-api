<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostStoreRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    /**
     * Retrieve a paginated list of all posts.
     *
     * @return JsonResponse|mixed
     */
    public function getPaginatedPost(): JsonResponse
    {
        try {
            $posts = Post::with('user')->paginate();
            return response()->json(new PostCollection($posts), 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving posts.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Retrieve a single post by its ID.
     *
     * @param int $id
     * @return JsonResponse|mixed
     */
    public function getPost(Post $post): JsonResponse
    {
        try {
            if (auth()->check() && auth()->id() !== $post->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to view this post or post does not exist.'
                ], 403);
            }

            $post->loadMissing('user');

            $cacheKey = "post_{$post->id}";
            $postData = Cache::remember($cacheKey, 900, fn() => new PostResource($post));

            return response()->json([
                'success' => true,
                'message' => 'Post retrieved successfully.',
                'data' => $postData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while retrieving the post.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new post.
     *
     * @param \App\Http\Requests\PostStoreRequest $request
     * @return JsonResponse|mixed
     */
    public function store(PostStoreRequest $request): JsonResponse
    {
        try {
            $post = Post::create([
                'title' => $request->input('title'),
                'content' => $request->input('content'),
                'user_id' => auth()->id()
            ]);

            $post->loadMissing('user');

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully.',
                'data' => new PostResource($post)
            ], 201);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the post.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
