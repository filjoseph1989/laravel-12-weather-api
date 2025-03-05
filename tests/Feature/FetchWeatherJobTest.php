<?php

namespace Tests\Feature;

use App\Jobs\FetchWeatherJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FetchWeatherJobTest extends TestCase
{
    use RefreshDatabase;

    /**
     * first we fake the HTTP request to the OpenWeatherMap API
     * then we dispatch the FetchWeatherJob
     * finally we assert that the data has been stored in the database
     *
     * @return void
     */
    public function test_weather_job_fetches_and_stores_data(): void
    {
        Http::fake([
            '*' => Http::response([
                "coord" => [
                    "lon" => 115.8606,
                    "lat" => -31.9559
                ],
                "weather" => [
                    [
                        "id" => 802,
                        "main" => "Clouds",
                        "description" => "scattered clouds",
                        "icon" => "03n"
                    ]
                ],
                "base" => "stations",
                "main" => [
                    "temp" => 294.08,
                    "feels_like" => 293.56,
                    "temp_min" => 292.09,
                    "temp_max" => 295.24,
                    "pressure" => 1022,
                    "humidity" => 51,
                    "sea_level" => 1022,
                    "grnd_level" => 1018
                ],
                "visibility" => 10000,
                "wind" => [
                    "speed" => 3.6,
                    "deg" => 150
                ],
                "clouds" => [
                    "all" => 36
                ],
                "dt" => 1741100174,
                "sys" => [
                    "type" => 2,
                    "id" => 63154,
                    "country" => "AU",
                    "sunrise" => 1741039696,
                    "sunset" => 1741085324
                ],
                "timezone" => 28800,
                "id" => 2061261,
                "name" => "South Perth",
                "cod" => 200
            ], 200),
        ]);

        FetchWeatherJob::dispatchSync('South Perth', 'AU');

        $this->assertDatabaseHas('weather', [
            'temperature' => 294.08,
            'description' => 'scattered clouds',
            'city' => 'South Perth',
        ]);
    }
}
