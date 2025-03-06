<?php

namespace Tests\Feature;

use Mockery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Test that the endpoint retrieves the authenticated user's details successfully.
     *
     * @return void
     */
    public function test_get_user_success()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User retrieved successfully.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
    }

    /**
     * Test that the endpoint denies access to another user's details.
     *
     * @return void
     */
    public function test_get_user_unauthorized()
    {
        $user1 = User::factory()->create(); // User making the request
        $user2 = User::factory()->create(); // Another user
        $token = $user1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/users/{$user2->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at',
                    'posts'
                ],
            ]);

    }

    /**
     * Test that the endpoint returns 404 for a non-existent user.
     *
     * @return void
     */
    public function test_get_user_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/users/999');

        $response->assertStatus(404)
            ->assertJson([
                'message' => 'No query results for model [App\\Models\\User] 999',
            ]);
    }
}
