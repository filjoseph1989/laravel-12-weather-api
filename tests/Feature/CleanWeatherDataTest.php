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

    /**
     * Remove all records from the weather table after each test.
     * 
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        Weather::truncate();
    }

    /**
     * Create old records and recent records in the weather table.
     * Call the clean-weather-data command.
     * Assert that the old records have been deleted.
     * Assert that the command output contains the expected message.
     *
     * @return void
     */
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
