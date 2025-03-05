<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Weather>
 */
class WeatherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city' => $this->faker->city,
            'temperature' => $this->faker->randomFloat(2, -50, 50),
            'description' => $this->faker->sentence,
            'humidity' => $this->faker->numberBetween(0, 100),
            'wind_speed' => $this->faker->randomFloat(2, 0, 100)
        ];
    }
}
