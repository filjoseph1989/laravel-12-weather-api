<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Http\Requests\PostStoreRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Log;

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
            Log::error('Error retrieving posts: ' . $th->getMessage());
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
            Log::error('Error retrieving post: ' . $th->getMessage());
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
            Log::error('Error creating post: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the post.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing post.
     *
     * @param \App\Models\Post $post
     * @param \App\Http\Requests\PostStoreRequest $request
     * @return JsonResponse|mixed
     */
    public function update(Post $post, PostStoreRequest $request): JsonResponse
    {
        try {
            Post::where('id', $post->id)->update([
                'title' => $request->input('title'),
                'content' => $request->input('content')
            ]);

            $post->loadMissing('user');
            $post->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully.',
                'data' => new PostResource($post)
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error updating post: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the post.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Delete an existing post.
     *
     * @param \App\Models\Post $post
     * @return JsonResponse|mixed
     */
    public function destroy(Post $post): JsonResponse
    {
        try {
            if (auth()->check() && auth()->id() !== $post->user_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to delete this post.'
                ], 403);
            }

            Post::destroy($post->id);

            Cache::forget("post_{$post->id}");

            return response()->json([
                'success' => true,
                'message' => 'Post deleted successfully.'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error deleting post: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the post.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
