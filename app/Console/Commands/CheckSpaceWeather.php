<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use App\Enums\SpaceWeatherCondition;
use App\Services\SpaceWeatherService;
use function Laravel\Prompts\warning;

class CheckSpaceWeather extends Command
{
    protected $signature = 'space:weather';
    protected $description = 'Check current space weather conditions';

    public function handle(SpaceWeatherService $weatherService)
    {
        $weather = $weatherService->getCurrentWeather();

        $this->displayWeatherInfo($weather);

        if ($weather->getDelay() > 0) {
            $this->displayAffectedRoutes();
        }
    }

    private function displayWeatherInfo($weather)
    {
        match($weather) {
            SpaceWeatherCondition::CLEAR => info("🌟 " . $weather->getDescription()),
            SpaceWeatherCondition::METEOR_STORM => error("☄️ " . $weather->getDescription()),
            SpaceWeatherCondition::SOLAR_FLARE => warning("☀️ " . $weather->getDescription()),
            SpaceWeatherCondition::COSMIC_RADIATION => warning("⚡ " . $weather->getDescription()),
            SpaceWeatherCondition::ION_STORM => error("🌪 " . $weather->getDescription()),
        };

        if ($weather->getDelay() > 0) {
            warning(sprintf(
                "Expected delays: %d hours %d minutes",
                floor($weather->getDelay() / 3600),
                ($weather->getDelay() % 3600) / 60
            ));
        }
    }

    private function displayAffectedRoutes()
    {
        $activeRoutes = \App\Models\TradeRoute::whereHas('starship', function($query) {
            $query->where('status', 'IN_TRANSIT');
        })->get();

        if ($activeRoutes->isEmpty()) {
            info('No active trade routes affected.');
            return;
        }

        info('Affected trade routes:');
        foreach ($activeRoutes as $route) {
            warning("- {$route->name} (Starship: {$route->starship->name})");
        }
    }
}
