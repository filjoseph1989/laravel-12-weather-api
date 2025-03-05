<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Tests\TestCase;
use App\Models\Weather;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;

class CleanWeatherDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_clean_weather_data_command_removes_old_records()
    {
        // Create old weather data
        Weather::factory()->create(['created_at' => Carbon::now()->subDays(8)]);
        Weather::factory()->count(3)->create(['created_at' => Carbon::now()->subDays(10)]);

        // Create recent weather data
        Weather::factory()->create(['created_at' => Carbon::now()->subDays(2)]);
        Weather::factory()->count(2)->create(['created_at' => Carbon::now()]);

        $this->assertDatabaseCount('weather', 7);

        Artisan::call('app:clean-weather-data');

        $this->assertDatabaseCount('weather',4);

        $output = Artisan::output();

        $this->assertStringContainsString('Old weather data deleted successfully.', $output);
    }
}
