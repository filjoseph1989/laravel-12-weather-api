<?php

namespace App\Jobs;

use App\Models\Weather;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Log;

class FetchWeatherJob implements ShouldQueue
{
    use Queueable;

    /**
     * The API key for OpenWeatherMap.
     */
    private string $apiKey = '';

    /**
     * Putting Perth here as the default city.
     */
    private string $city = "Perth";

    /**
     * Setting Australia as the default country.
     */
    private string $country = "AU";

    /**
     * The endpoint for the OpenWeatherMap API.
     * @var string
     */
    private string $endpoint = '';

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->apiKey = env('OPENWEATHERMAP_API_KEY');
        $this->endpoint = env('OPENWEATHERMAP_URL');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Fetching weather data for city: ' . $this->city);

        $response = Http::get($this->endpoint, [
            'q' => "{$this->city},{$this->country}",
            'appid' => $this->apiKey,
            'units' => 'metric'
        ]);

        if ($response->failed()) {
            Log::error('Failed to retrieve weather data for city: ' . $this->city);
            throw new \Exception('Failed to retrieve weather data from OpenWeatherMap API.');
        }

        $data = $response->json();

        $weatherData = [
            'temperature' => $data['main']['temp'],
            'description' => $data['weather'][0]['description'],
            'humidity' => $data['main']['humidity'],
            'wind_speed' => $data['wind']['speed'],
            'city' => $data['name'],
        ];

        Log::info('Weather data retrieved for city: ' . $weatherData['city']);

        try {
            Weather::create($weatherData);
            Log::info('Weather data successfully saved for city: ' . $weatherData['city']);
        } catch (\Throwable $th) {
            Log::error('Failed to save weather data for city: ' . $weatherData['city']);
            Log::error($th->getMessage());
            throw $th;
        }

        return;
    }
}