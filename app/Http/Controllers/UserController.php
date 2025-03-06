<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use AuthorizesRequests, ApiResponse;

    /**
     * The user service instance.
     * @var
     */
    protected $service;

    public function __construct()
    {
        $this->service = new UserService();
    }

    /**
     * Return information about the current user
     *
     * @param \App\Models\User $user
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getUser(User $user): JsonResponse
    {
        try {
            $this->authorize('view', $user);
            $userData = $this->service->getUser($user);
            return $this->successResponse($userData, 'User retrieved successfully.');
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to retrieve user.', $th->getMessage(), 500);
        }
    }
}