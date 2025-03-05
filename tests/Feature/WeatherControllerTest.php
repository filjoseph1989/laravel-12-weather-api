<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Weather;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * First, create a user token to access the weather API.
     * Next, create a dummy record in the weather table to populate the database with data.
     * Finally, make a request to the endpoint and verify the structure and values of the response.
     *
     * @return void
     */
    public function test_weather_api_returns_data()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Weather::create([
            'temperature' => 294.08,
            'description' => 'scattered clouds',
            'city' => 'South Perth',
            'humidity' => 51,
            'wind_speed' => 3.6,
        ]);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/weather');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'city',
                    'temperature',
                    'description',
                    'humidity',
                    'wind_speed',
                ],
            ])->assertJson([
                'success' => true,
                'data' => [
                    'temperature' => 294.08,
                    'description' => 'scattered clouds',
                    'city' => 'South Perth',
                    'humidity' => 51,
                    'wind_speed' => 3.6,
                ],
            ]);
    }

    /**
     * First, create a user token to access the weather API.
     * Next, don't create a dummy record in the weather table.
     * Finally, make a request to the endpoint and verify the response.
     *
     * @return void
     */
    public function test_get_weather_returns_error_when_no_data_in_database()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])->getJson('/api/weather');

        $response->assertStatus(500)
            ->assertJson([
                'success' => false,
                'message' => 'Failed to retrieve weather data.',
            ]);
    }
}
