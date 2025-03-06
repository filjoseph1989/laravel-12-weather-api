<?php

namespace App\Http\Controllers;

use Log;
use App\Models\User;
use App\Jobs\SendWelcomeEmail;
use App\Http\Requests\AuthRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Login a user and return a token.
     *
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
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
    }

    /**
     * Logout a user and revoke their token.
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function logout()
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
    public function register(AuthRequest $request): \Illuminate\Http\JsonResponse
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
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while registering the user.',
                'error' => $th->getMessage()
            ], 500);
        }
    }
}
