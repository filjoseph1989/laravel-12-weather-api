<?php

namespace App\Http\Controllers;

use Log;
use App\Models\Post;
use App\Services\PostService;
use App\Traits\ApiResponse;
use App\Http\Requests\PostStoreRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PostController extends Controller
{
    use AuthorizesRequests, ApiResponse;

    private $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    /**
     * Retrieve a paginated list of all posts.
     *
     * @return JsonResponse|mixed
     */
    public function getPaginatedPost(): JsonResponse
    {
        try {
            $posts = $this->postService->getPaginatedPosts();
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
            $this->authorize('view', $post);
            $post = $this->postService->getPost($post);
            return $this->successResponse(new PostResource($post), 'Post retrieved successfully.');
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
            $post = $this->postService->createPost($request->all(), auth()->id());
            return $this->successResponse(new PostResource($post), 'Post created successfully.', 201);
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
    public function update(int $id, PostStoreRequest $request): JsonResponse
    {
        try {
            // lets make sure post exists
            if (Post::where('id', $id)->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found.'
                ], 404);
            }

            $post = Post::where('id', $id)->first();

            $this->authorize('update', $post);

            $post = $this->postService->updatePost($post, $request->all());
            return $this->successResponse(new PostResource($post), 'Post updated successfully.');
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
    public function destroy(int $id): JsonResponse
    {
        try {
            // lets make sure post exists
            if (Post::where('id', $id)->count() === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Post not found.'
                ], 404);
            }

            $post = Post::where('id', $id)->first();

            $this->authorize('delete', $post);
            $this->postService->deletePost($post);
            return $this->successResponse(null, 'Post deleted successfully.');
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
