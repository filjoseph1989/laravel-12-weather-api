<?php

namespace App\Console\Commands;

use App\Models\Weather;
use Illuminate\Console\Command;

class CleanWeatherData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-weather-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove old weather data from the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Weather::where('created_at', '<', now()->subDays(7))->delete();
        $this->info('Old weather data deleted successfully.');
    }
}
