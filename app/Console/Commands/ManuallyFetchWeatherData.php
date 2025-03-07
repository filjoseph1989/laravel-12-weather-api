<?php

namespace App\Console\Commands;

use App\Jobs\FetchWeatherJob;
use Illuminate\Console\Command;

class ManuallyFetchWeatherData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:manually-fetch-weather-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch weather data manually.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running jobs manually...');
        FetchWeatherJob::dispatch();
        $this->info('Weather job has been dispatched successfully.');
    }
}
