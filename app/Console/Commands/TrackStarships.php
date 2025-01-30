<?php

namespace App\Console\Commands;

use App\Enums\StarshipStatus;
use App\Models\Starship;
use Illuminate\Console\Command;
use function Laravel\Prompts\table;
use function Laravel\Prompts\info;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\select;

class TrackStarships extends Command
{
    protected $signature = 'starships:track';
    protected $description = 'Track starships status in real-time';

    public function handle()
    {
        while (true) {
            $action = select(
                'Starship Tracking System',
                [
                    'refresh' => 'Refresh Status',
                    'filter' => 'Filter by Status',
                    'exit' => 'Exit Tracking',
                ]
            );

            if ($action === 'exit') {
                break;
            }

            if ($action === 'filter') {
                $status = select(
                    'Filter by status',
                    [
                        'all' => 'All Statuses',
                        StarshipStatus::IDLE->value => 'Idle Starships',
                        StarshipStatus::IN_TRANSIT->value => 'In Transit',
                        StarshipStatus::UNDER_MAINTENANCE->value => 'Under Maintenance',
                    ]
                );
                $this->displayStarships($status);
            } else {
                $this->displayStarships();
            }
        }
    }

    private function displayStarships(?string $statusFilter = 'all')
    {
        $starships = spin(function () use ($statusFilter) {
            $query = Starship::with(['assignedRoute']);

            if ($statusFilter !== 'all') {
                $query->where('status', $statusFilter);
            }

            return $query->get()->map(function ($starship) {
                return [
                    'Name' => $starship->name,
                    'Status' => $starship->status->label(),
                    'Route' => $starship->assignedRoute?->name ?? 'None',
                    'Cargo' => $starship->cargo_capacity . ' tons',
                    'Last Updated' => $starship->updated_at->diffForHumans(),
                ];
            })->toArray();
        }, 'Fetching starship data...');

        if (empty($starships)) {
            info('No starships found with the selected criteria.');
            return;
        }

        table(
            ['Name', 'Status', 'Route', 'Cargo', 'Last Updated'],
            $starships
        );
    }
}
