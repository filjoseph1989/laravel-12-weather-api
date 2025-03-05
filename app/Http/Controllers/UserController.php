<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
class UserController extends Controller
{
    use AuthorizesRequests, ApiResponse;

    /**
     * Return information about the current user
     *
     * @param \App\Models\User $user
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getUser(User $user)
    {
        try {
            $this->authorize('view', $user);

            $user->loadMissing(['posts']);

            $cacheKey = "user_{$user->id}";
            $userData = Cache::remember($cacheKey, 900, fn() => new UserResource($user));

            return $this->successResponse($userData, 'User retrieved successfully.');
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to retrieve user.', $th->getMessage(), 500);
        }
    }
}