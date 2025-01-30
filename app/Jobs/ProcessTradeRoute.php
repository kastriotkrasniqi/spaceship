<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Starship;
use App\Models\TradeRoute;
use App\Enums\StarshipStatus;
use Illuminate\Bus\Queueable;
use App\Models\TradeAgreement;
use App\Jobs\CompleteTradeRoute;
use App\Events\StarshipStatusUpdated;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\SpaceWeatherService;

class ProcessTradeRoute implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected TradeAgreement $agreement
    ) {}

    public function handle(): void
    {
        $starship = Starship::where('status', StarshipStatus::IDLE)
            ->where('cargo_capacity', '>=', $this->agreement->quantity)
            ->first();

        if (!$starship) {
            $this->release(3600); // Try again in 1 hour if no starship available
            return;
        }

        $trade_route = TradeRoute::create([
            'name' => $this->agreement->origin->name.' to '.$this->agreement->destination->name.' agreement',
            'origin_id' => $this->agreement->origin_id,
            'destination_id' => $this->agreement->destination_id,
            'resource_id' => $this->agreement->resource_id,
            'quantity' => $this->agreement->quantity,
            'travel_time' => $this->agreement->travel_time,
        ]);

        $starship->update([
            'status' => StarshipStatus::IN_TRANSIT->value,
            'assigned_route_id' => $trade_route->id
        ]);

        event(new StarshipStatusUpdated($starship));

        $weatherService = app(SpaceWeatherService::class);
        $currentWeather = $weatherService->getCurrentWeather();
        $weatherDelay = $currentWeather->getDelay();

        $totalDelay = ($trade_route->travel_time * 3600) + $weatherDelay;

        CompleteTradeRoute::dispatch($starship)->delay($totalDelay);

        if ($weatherDelay > 0) {
            info("Trade route delayed by weather conditions: " . $currentWeather->getDescription());
        }

        $this->agreement->update([
            'next_delivery' => Carbon::parse($this->agreement->next_delivery)->addDays($this->agreement->frequency)
        ]);
    }

    public function failed(\Throwable $exception)
    {
        \Log::error('Trade route processing failed: ' . $exception->getMessage());
    }
}
