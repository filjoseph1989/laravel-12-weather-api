<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function getUser(User $user)
    {
        try {
            $cacheKey = "user_{$user->id}";
            $userData = Cache::remember($cacheKey, 900, fn() => new UserResource($user));
            return response()->json([
                'success' => true,
                'message' => 'User retrieved successfully.',
                'data' => $userData
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}