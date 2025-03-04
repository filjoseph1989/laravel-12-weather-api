<?php

namespace App\Http\Controllers;

use App\Http\Requests\WeatherRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class WeatherController extends Controller
{
    private string $apiKey = '';
    private string $city = "Perth";
    private string $country = "AU";

    public function __construct()
    {
        $this->apiKey = env('OPENWEATHERMAP_API_KEY');
    }

    public function getWeather(WeatherRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->city = $request->input('city', $this->city);
            $this->country = $request->input('country', $this->country);
            $cacheKey = "weather_{$this->city}_{$this->country}";

            $weatherData = Cache::remember($cacheKey, 900, function() {
                $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                    'q' => "{$this->city},{$this->country}",
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]);

                if ($response->failed()) {
                    throw new \Exception('Failed to retrieve weather data.');
                }

                $data = $response->json();

                return [
                    'temperature' => $data['main']['temp'],
                    'description' => $data['weather'][0]['description'],
                    'humidity' => $data['main']['humidity'],
                    'wind_speed' => $data['wind']['speed'],
                    'timestamp' => now()->toDateTimeString(),
                    'city' => $data['name'],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $weatherData
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve weather data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
