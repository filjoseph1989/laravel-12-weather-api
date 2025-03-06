<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Tests\TestCase;
use App\Http\Controllers\PostController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Remove all records from the weather table after each test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Post::truncate();
    }

    /**
     * Test that the controller returns a paginated list of posts.
     *
     * @return void
     */
    public function test_it_retrieves_paginated_posts()
    {
        $user = User::factory()->create();

        Post::factory()->count(5)->create([
            'user_id' => $user->id
        ]);

        $controller = new PostController();
        $response = $controller->getPaginatedPost();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getContent();
        $data = json_decode($data, true);

        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('links', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertIsArray($data['data']);
        $this->assertIsArray($data['links']);
        $this->assertIsArray($data['meta']);
        $this->assertCount(5, $data['data']);
        $this->assertCount(4, $data['links']);
        $this->assertCount(7, $data['meta']);

        foreach ($data['data'] as $post) {
            $this->assertArrayHasKey('id', $post);
            $this->assertArrayHasKey('title', $post);
            $this->assertArrayHasKey('content', $post);
            $this->assertArrayHasKey('author', $post);
            $this->assertArrayHasKey('created_at', $post);
            $this->assertArrayHasKey('updated_at', $post);
            $this->assertNotEmpty($post['title']);
            $this->assertNotEmpty($post['content']);
            $this->assertNotEmpty($post['author']);
            $this->assertNotEmpty($post['created_at']);
            $this->assertNotEmpty($post['updated_at']);
        }
    }

    /**
     * Test that the controller returns an empty array when no posts are found.
     *
     * @return void
     */
    public function test_it_return_empty_array_when_no_post()
    {
        $response = (new PostController())->getPaginatedPost();
        $this->assertInstanceOf(JsonResponse::class,$response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('message', $data);
        $this->assertArrayHasKey('data', $data);

        $this->assertCount(0, $data['data']);
        $this->assertEquals('No posts found.', $data['message']);

        $this->assertEquals(true, $data['success']);
    }
}
