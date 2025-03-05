<?php

namespace App\Http\Controllers;

use App\Http\Requests\WeatherRequest;
use App\Models\Weather;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Log;

class WeatherController extends Controller
{
    /**
     * The API key for OpenWeatherMap.
     */
    public function __construct()
    {
        $this->apiKey = env('OPENWEATHERMAP_API_KEY');
    }

    /**
     * Get the weather information for a specific city and country.
     *
     * @throws \Exception
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getWeather(): \Illuminate\Http\JsonResponse
    {
        try {
            $weatherData = Cache::remember('weather_data', 900, function() {
                $weatherData = Weather::latest()->first();

                if (!$weatherData) {
                    throw new \Exception('Weather data not found in the database.');
                }

                Log::info('Weather data successfully retrieved and cached.');

                return $weatherData;
            });


            Log::info('Weather data successfully retrieved and cached.');

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
