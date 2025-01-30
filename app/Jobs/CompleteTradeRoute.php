<?php

namespace App\Jobs;

use App\Models\Starship;
use App\Events\StarshipStatusUpdated;
use App\Enums\StarshipStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CompleteTradeRoute implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected Starship $starship
    ) {}

    public function handle(): void
    {
        $this->starship->update([
            'status' => StarshipStatus::IDLE,
            'assigned_route_id' => null
        ]);

        event(new StarshipStatusUpdated($this->starship));
    }
}
