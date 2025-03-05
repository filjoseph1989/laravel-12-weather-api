<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostCollection;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Retrieve a paginated list of all posts.
     *
     * @return JsonResponse|mixed
     */
    public function allPost(): JsonResponse
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
}
