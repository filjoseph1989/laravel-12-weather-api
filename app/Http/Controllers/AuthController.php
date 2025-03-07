<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Log;
use App\Models\User;
use App\Jobs\SendWelcomeEmail;
use App\Http\Requests\AuthRequest;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    /**
     * Login a user and return a token.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            if (!auth()->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials.'
                ], 401);
            }

            $token = auth()->user()->createToken('authToken')->plainTextToken;

            return response()->json([
                'success' => true,
                'token' => $token
            ], 200);
        } catch (\Throwable $th) {
            Log::error('An error occurred while logging in: ' . $th->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while logging in.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Logout a user and revoke their token.
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated.'
            ], 401);
        }

        try {
            auth()->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully.'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('An error occurred while logging out: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while logging out.',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Register a new user and return a token.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
            ]);

            $token = $user->createToken('authToken')->plainTextToken;

            SendWelcomeEmail::dispatch($user, $token);

            Log::info('Dispatched welcome email for user: ' . $user->email);

            return response()->json([
                'success' => true,
                'token' => $token
            ], 201);
        } catch (\Throwable $th) {
            Log::error('An error occurred while registering the user: ' . $th->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while registering the user.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
