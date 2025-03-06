<?php

namespace Tests\Feature;

use Log;
use Cache;
use Mockery;
use App\Models\Post;
use App\Models\User;
use App\Http\Requests\PostStoreRequest;
use App\Http\Resources\PostResource;
use App\Http\Controllers\PostController;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Remove all records from the weather table after each test.
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * This is to check that the endpoint returns a paginated list of posts
     *
     * @return void
     */
    public function test_api_retrieves_paginated_posts()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Post::factory()->count(5)->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/posts');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'content',
                    'author',
                    'created_at',
                    'updated_at'
                ]
            ],
            'links',
            'meta'
        ]);
    }

    /**
     * The purpose of this is to check that the endpoint return empty array
     *
     * @return void
     */
    public function test_api_returns_empty_array_when_no_posts()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")->getJson('/api/posts');
        $response->assertStatus(200);
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
        $this->assertCount(0, $data['data']);
        $this->assertEquals(true, $data['success']);
    }

    /**
     * This is to check that the endpoint returns a single post
     *
     * @return void
     */
    public function test_get_a_single_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Post retrieved successfully.',
            'data' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'author' => $user->name,
            ],
        ]);
    }

    /**
     * This is to check that the endpoint do not return a single post
     * because the user is not authorized
     *
     * @return void
     */
    public function test_get_a_single_post_unauthorized()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user1->id]);
        $token = $user2->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/posts/{$post->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You are not authorized to view this post or post does not exist.',
        ]);
    }

    /**
     * This is to check that the endpoint do not return a single post
     * because the post does not exist
     *
     * @return void
     */
    public function test_get_a_single_post_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/posts/999");
        $response->assertStatus(404);
    }

    /**
     * This is to check that the endpoint as it handles an exception
     *
     * @return void
     */
    public function test_get_a_single_post_return_error_on_exception()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        Cache::shouldReceive('remember')
            ->andThrow(new \Exception('Cache error'));

        Log::shouldReceive('error')
            ->once()
            ->with(Mockery::pattern('/Error retrieving post: Cache error/'));

        $controller = new PostController();

        $response = $controller->getPost($post);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());

        $responseData = $response->getData(true);
        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('error', $responseData);

        $this->assertFalse($responseData['success']);
        $this->assertEquals('An error occurred while retrieving the post.', $responseData['message']);
        $this->assertEquals('Cache error', $responseData['error']);

        Mockery::close();
    }

    /**
     * This is to check that the endpoint returning post from cache
     *
     * @return void
     */
    public function test_get_a_single_post_cache()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $response1 = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/posts/{$post->id}");
        $response1->assertStatus(200);

        Cache::shouldReceive('remember')->once()->andReturn(new PostResource($post));

        $response2 = $this->withHeader('Authorization', "Bearer $token")->getJson("/api/posts/{$post->id}");

        $response2->assertStatus(200);
    }

    /**
     * This is to check that the endpoint if it can store new posts
     *
     * @return void
     */
    public function test_store_post_success()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $postData = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/posts', $postData);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'message' => 'Post created successfully.',
        ]);

        $this->assertDatabaseHas('posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
            'user_id' => $user->id
        ]);

        $responseData = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals($postData['title'], $responseData['data']['title']);
        $this->assertEquals($postData['content'], $responseData['data']['content']);
        $this->assertEquals($user->name, $responseData['data']['author']);
    }

    /**
     * This is to check that the endpoint if the user that is not authenticated should be able to
     * store new posts
     *
     * @return void
     */
    public function test_store_post_unauthenticated()
    {
        $postData = [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);

        $this->assertDatabaseMissing('posts', [
            'title' => 'Test Post',
            'content' => 'This is a test post content.',
        ]);
    }

    /**
     * This is to test that the endpoint reject information that is not valid
     *
     * @return void
     */
    public function test_store_post_validation_error()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $postData = [
            'title' => '', // title is required at will cause validation to fail
            'content' => 'This is a test post content.',
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/posts', $postData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
        $this->assertDatabaseMissing('posts', [
            'content' => 'This is a test post content.',
        ]);
    }

    /**
     * This is to check that the endpoint handles updating existing posts
     *
     * @return void
     */
    public function test_update_post_success()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")->putJson("api/posts/{$post->id}", [
            'title' => 'Updated Post',
            'content' => 'Updated content',
        ]);

        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('Post updated successfully.', $responseData['message']);
        $this->assertArrayHasKey('data', $responseData);
        $this->assertEquals('Updated Post', $responseData['data']['title']);
        $this->assertEquals('Updated content', $responseData['data']['content']);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Post',
            'content' => 'Updated content',
        ]);
    }


    public function test_update_post_unauthenticated()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $updatedData = [
            'title' => 'Updated Test Post',
            'content' => 'This is the updated test post content.',
        ];

        $response = $this->putJson("/api/posts/{$post->id}", $updatedData);

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);

        $this->assertDatabaseMissing('posts', [
            'title' => 'Updated Test Post',
            'content' => 'This is the updated test post content.',
        ]);
    }

    public function test_update_post_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $updatedData = [
            'title' => 'Updated Test Post',
            'content' => 'This is the updated test post content.',
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/posts/999", $updatedData);

        $responseData = json_decode($response->getContent(), true);

        $response->assertStatus(404);

        $this->assertArrayHasKey('success', $responseData);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Post not found.', $responseData['message']);
        $this->assertEquals(false, $responseData['success']);
    }

    public function test_update_post_validation_error()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $post = Post::factory()->create(['user_id' => $user->id]);

        $updatedData = [
            'title' => '',
            'content' => 'This is the updated test post content.',
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/posts/{$post->id}", $updatedData);

        $response->assertStatus(422);

        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(false, $responseData["success"]);
        $this->assertEquals("Validation failed.", $responseData["message"]);
        $this->assertEquals("The title field is required.", $responseData["errors"]["title"][0]);
    }

    public function test_update_post_unauthorized()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user1->id]);
        $token = $user2->createToken('test')->plainTextToken;

        $updatedData = [
            'title' => 'Updated Test Post',
            'content' => 'This is the updated test post content.',
        ];

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/posts/{$post->id}", $updatedData);

        $response->assertStatus(403);
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals(false, $responseData['success']);
        $this->assertEquals("You are not authorized to delete this post.", $responseData['message']);
    }

    public function test_destroy_post_success()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Post deleted successfully.',
        ]);

        $this->assertSoftDeleted('posts', ['id' => $post->id]);
    }

    public function test_destroy_post_unauthorized()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user1->id]);
        $token = $user2->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You are not authorized to delete this post.',
        ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_destroy_post_unauthenticated()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->deleteJson("/api/posts/{$post->id}");

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Unauthenticated.'
        ]);

        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_destroy_post_not_found()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")->deleteJson("/api/posts/999");

        $response->assertStatus(404);
        $response->assertJson([
            'success' => false,
            'message' => 'Post not found.',
        ]);
    }

    public function test_destroy_post_cache_is_cleared()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $post = Post::factory()->create(['user_id' => $user->id]);

        Cache::shouldReceive('forget')->once()->with("post_{$post->id}");

        $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/posts/{$post->id}");
    }
}
