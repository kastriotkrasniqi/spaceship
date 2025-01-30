<?php

namespace App\Services;

use App\Enums\SpaceWeatherCondition;
use Illuminate\Support\Facades\Cache;

class SpaceWeatherService
{
    public function getCurrentWeather()
    {
        return Cache::remember('space_weather', 3600, function () {
            return $this->generateWeather();
        });
    }

    public function generateWeather()
    {
        $conditions = SpaceWeatherCondition::cases();
        $weights = [
            SpaceWeatherCondition::CLEAR->value => 70,
            SpaceWeatherCondition::METEOR_STORM->value => 10,
            SpaceWeatherCondition::SOLAR_FLARE->value => 8,
            SpaceWeatherCondition::COSMIC_RADIATION->value => 7,
            SpaceWeatherCondition::ION_STORM->value => 5,
        ];

        $random = random_int(1, 100);
        $cumulative = 0;

        foreach ($weights as $condition => $weight) {
            $cumulative += $weight;
            if ($random <= $cumulative) {
                return SpaceWeatherCondition::from($condition);
            }
        }

        return SpaceWeatherCondition::CLEAR;
    }
}