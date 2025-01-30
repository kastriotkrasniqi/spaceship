<?php

namespace App\Console\Commands;

use App\Models\TradeRoute;
use App\Models\Resource;
use App\Models\Planet;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\info;

class TradeAnalytics extends Command
{
    protected $signature = 'trade:analytics';
    protected $description = 'View trade statistics and analytics';

    public function handle()
    {
        $metric = select(
            'Select analytics metric to view:',
            [
                'busiest_routes' => 'Busiest Trade Routes',
                'most_traded' => 'Most Traded Resources',
                'planet_activity' => 'Planet Trading Activity',
                'active_starships' => 'Active Starships Overview',
            ]
        );

        match ($metric) {
            'busiest_routes' => $this->showBusiestRoutes(),
            'most_traded' => $this->showMostTradedResources(),
            'planet_activity' => $this->showPlanetActivity(),
            'active_starships' => $this->showActiveStarships(),
        };
    }

    private function showBusiestRoutes()
    {
        $routes = TradeRoute::select('name', 'quantity')
            ->orderByDesc('quantity')
            ->limit(10)
            ->get()
            ->map(fn($route) => [
                'Route' => $route->name,
                'Quantity' => number_format($route->quantity),
            ])
            ->toArray();

        info('Top 10 Busiest Trade Routes');
        table(
            ['Route', 'Quantity'],
            $routes
        );
    }

    private function showMostTradedResources()
    {
        $resources = TradeRoute::selectRaw('resources.name, SUM(trade_routes.quantity) as total_quantity')
            ->join('resources', 'resources.id', '=', 'trade_routes.resource_id')
            ->groupBy('resources.id', 'resources.name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get()
            ->map(fn($resource) => [
                'Resource' => $resource->name,
                'Total Quantity' => number_format($resource->total_quantity),
            ])
            ->toArray();

        info('Top 10 Most Traded Resources');
        table(
            ['Resource', 'Total Quantity'],
            $resources
        );
    }

    private function showPlanetActivity()
    {
        $planets = Planet::selectRaw('
            planets.name,
            COUNT(DISTINCT CASE WHEN trade_routes.origin_id = planets.id THEN trade_routes.id END) as exports,
            COUNT(DISTINCT CASE WHEN trade_routes.destination_id = planets.id THEN trade_routes.id END) as imports
        ')
            ->leftJoin('trade_routes', function($join) {
                $join->on('planets.id', '=', 'trade_routes.origin_id')
                    ->orOn('planets.id', '=', 'trade_routes.destination_id');
            })
            ->groupBy('planets.id', 'planets.name')
            ->orderByRaw('(exports + imports) DESC')
            ->limit(10)
            ->get()
            ->map(fn($planet) => [
                'Planet' => $planet->name,
                'Exports' => $planet->exports,
                'Imports' => $planet->imports,
                'Total Activity' => $planet->exports + $planet->imports,
            ])
            ->toArray();

        info('Top 10 Most Active Planets');
        table(
            ['Planet', 'Exports', 'Imports', 'Total Activity'],
            $planets
        );
    }

    private function showActiveStarships()
    {
        $starships = \App\Models\Starship::selectRaw('
            starships.name,
            starships.cargo_capacity,
            starships.status,
            trade_routes.name as current_route
        ')
            ->leftJoin('trade_routes', 'trade_routes.id', '=', 'starships.assigned_route_id')
            ->orderBy('starships.status')
            ->get()
            ->map(fn($starship) => [
                'Starship' => $starship->name,
                'Capacity' => number_format($starship->cargo_capacity),
                'Status' => $starship->status->label(),
                'Current Route' => $starship->current_route ?? 'None',
            ])
            ->toArray();

        info('Starships Status Overview');
        table(
            ['Starship', 'Capacity', 'Status', 'Current Route'],
            $starships
        );
    }
}
