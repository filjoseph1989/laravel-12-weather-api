<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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

    public function getWeather(): \Illuminate\Http\JsonResponse
    {
        try {
            $weatherData = Cache::remember('weatherData', 900, function() {
                $response = Http::get("https://api.openweathermap.org/data/2.5/weather?q={$this->city}&appid={$this->apiKey}");
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
