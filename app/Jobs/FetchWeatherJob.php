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
     * The number of times the job may be attempted.
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job should wait before retrying.
     * @var int
     */
    public $backoff = 60;

    /**
     * The API key for OpenWeatherMap.
     */
    private string $apiKey;

    /**
     * Default city, fetched from the environment or set to Perth.
     */
    private string $city;

    /**
     * Setting Australia as the default country.
     */
    private string $country;

    /**
     * The endpoint for the OpenWeatherMap API.
     * @var string
     */
    private string $endpoint;

    /**
     * Create a new job instance.
     */
    public function __construct(string $city = null, string $country = null)
    {
        $this->apiKey = env('OPENWEATHERMAP_API_KEY');
        $this->endpoint = env('OPENWEATHERMAP_URL');
        $this->city = $city ?? env('DEFAULT_CITY') ?? 'Perth';
        $this->country = $country ?? env('DEFAULT_COUNTRY') ?? 'AU';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        try {
            Log::info('Fetching weather data for city: ' . $this->city);

            $response = Http::timeout(10)->get($this->endpoint, [
                'q' => "{$this->city},{$this->country}",
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);

            if ($response->failed()) {
                Log::error('Failed to retrieve weather data for city: ' . $this->city);
                $this->fail(new \Exception('Failed to retrieve weather data from OpenWeatherMap API.')); # This is to respect retry settings
                return;
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

            Weather::create($weatherData);

            Log::info('Weather data successfully saved for city: ' . $weatherData['city']);
        } catch (\Throwable $th) {
            Log::error('Failed to save weather data for city: ' . $this->city . ' - ' . $th->getMessage());
            $this->fail($th); # This is to respect retry settings
        }

        Log::info('FetchWeatherJob completed for city: ' . $this->city);

        return;
    }

    /**
     * Summary of failed job execution handling.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('FetchWeatherJob failed for city: ' . $this->city, ['error' => $exception->getMessage()]);
    }
}