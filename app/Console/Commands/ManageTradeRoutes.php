<?php

namespace App\Console\Commands;

use App\Models\Planet;
use App\Models\Resource;
use App\Models\Inventory;
use App\Models\TradeRoute;
use Illuminate\Console\Command;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\table;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\confirm;

class ManageTradeRoutes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'traderoutes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Trade Routes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $function = select(
            label: 'What would you like to do?',
            options: ['Add Trade Route', 'Show Trade Routes', 'Update Trade Route', 'Delete Trade Route', 'Exit'],
            required: true
        );

        match ($function) {
            'Add Trade Route' => $this->create(),
            'Show Trade Routes' => $this->index(),
            'Delete Trade Route' => $this->destroy(),
            'Update Trade Route' => $this->update(),
            'Exit' => exit(),
        };
    }

    public function index()
    {
        $tradeRoutes = TradeRoute::with(['origin', 'destination', 'resource'])->get();

        if ($tradeRoutes->isEmpty()) {
            info('No trade routes found.');
            return;
        }

        $tradeRoutesData = $tradeRoutes->map(function ($route) {
            return [
                'ID' => $route->id,
                'Name' => $route->name,
                'Origin' => $route->origin->name,
                'Destination' => $route->destination->name,
                'Resource' => $route->resource->name,
                'Quantity' => $route->quantity,
                'Travel Time' => $route->travel_time . ' hours',
            ];
        })->toArray();

        table(['ID', 'Name', 'Origin', 'Destination', 'Resource', 'Quantity', 'Travel Time'], $tradeRoutesData);

        $this->handle();
    }

    public function create()
    {
        $name = text('Enter the name of the trade route:');

        $originPlanetId = search(
            label: 'Search for the origin planet',
            placeholder: 'E.g. Earth',
            options: fn(string $value) => strlen($value) > 0
            ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
            : Planet::pluck('name', 'id')->all(),
        );

        if (!$originPlanetId) {
            error('No origin planet selected. Operation cancelled.');
            return;
        }

        $destinationPlanetId = search(
            label: 'Search for the destination planet',
            placeholder: 'E.g. Mars',
            options: function (string $value) use ($originPlanetId) {
                return strlen($value) > 0
                    ? Planet::whereLike('name', "%{$value}%")
                        ->pluck('name', 'id')
                        ->except([$originPlanetId])
                        ->all()
                    : Planet::pluck('name', 'id')
                        ->except([$originPlanetId])
                        ->all();
            }
        );
        ;

        if (!$destinationPlanetId) {
            error('No destination planet selected. Operation cancelled.');
            return;
        }

        // Check if the origin planet has any resources available
        $resourceOptions = Inventory::where('planet_id', $originPlanetId)
            ->pluck('resource_id')->unique()
            ->mapWithKeys(function ($resourceId) {
                return [$resourceId => Resource::find($resourceId)->name];
            })->toArray();

        if (empty($resourceOptions)) {
            error('The origin planet has no available resources for trade.');
            return;
        }

        $resourceId = select(
            label: 'Select a resource to trade',
            options: $resourceOptions,
            required: true
        );

        if (!$resourceId) {
            error('No resource selected. Operation cancelled.');
            return;
        }

        $inventory = Inventory::where('planet_id', $originPlanetId)
            ->where('resource_id', $resourceId)
            ->first();

        if (!$inventory) {
            error('The selected resource is not available in the planet\'s inventory.');
            return;
        }

        info("Available quantity for resource '{$inventory->resource->name}': {$inventory->quantity}");

        $quantity = text('Enter the quantity of the resource to trade:');

        if (!is_numeric($quantity) || $quantity <= 0) {
            error("Quantity must be a positive number.");
            return;
        }

        if ($quantity > $inventory->quantity) {
            error("The quantity entered exceeds the available stock. Available quantity: {$inventory->quantity}");
            return;
        }

        $inventory->quantity -= $quantity;
        $inventory->save();

        $travelTime = text('Enter the travel time (in hours):');
        if (!is_numeric($travelTime) || $travelTime <= 0) {
            error('Travel time must be a positive number.');
            return;
        }

        $tradeRoute = TradeRoute::create([
            'name' => $name,
            'origin_id' => $originPlanetId,
            'destination_id' => $destinationPlanetId,
            'resource_id' => $resourceId,
            'quantity' => $quantity,
            'travel_time' => $travelTime,
        ]);

        info("Trade Route '{$name}' created successfully with origin {$originPlanetId}, destination {$destinationPlanetId}, resource {$resourceId}, quantity {$quantity}, and travel time {$travelTime} hours.");

        info('Resources Left : ' . $inventory->quantity);

        $this->handle();
    }


    public function update()
    {
        $tradeRouteId = search(
            label: 'Search for the trade route to update',
            placeholder: 'E.g. Saturn to Mars',
            options: fn(string $value) => strlen($value) > 0
            ? TradeRoute::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
            :  TradeRoute::pluck('name', 'id')->all(),
        );

        if (!$tradeRouteId) {
            error("No trade route selected. Operation cancelled.");
            return;
        }

        $tradeRoute = TradeRoute::find($tradeRouteId);

        $fieldsToUpdate = multiselect(
            label: 'Select fields to update',
            options: [
                'name' => 'Name',
                'origin_id' => 'Origin Planet',
                'destination_id' => 'Destination Planet',
                'resource_id' => 'Resource',
                'quantity' => 'Quantity',
                'travel_time' => 'Travel Time',
            ]
        );

        if (empty($fieldsToUpdate)) {
            error("No fields selected. Operation cancelled.");
            return;
        }

        $oldName = $tradeRoute->name;
        if (in_array('name', $fieldsToUpdate)) {
            $newName = text('Enter the new name for the trade route:');

            if (empty($newName)) {
                error('Name cannot be empty. Operation cancelled.');
                return;
            }

            $tradeRoute->name = $newName;

        }

        if (in_array('origin_id', $fieldsToUpdate)) {
            do {
                $originPlanetId = search(
                    label: 'Search for the origin planet',
                    placeholder: 'E.g. Earth',
                    options: fn(string $value) => strlen($value) > 0
                    ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                    : Planet::pluck('name', 'id')->all(),
                );

                if (!$originPlanetId) {
                    error('No origin planet selected. Operation cancelled.');
                    return;
                }

                $availableResources = Inventory::where('planet_id', $originPlanetId)->exists();

                if (!$availableResources) {
                    $changeOrigin = confirm("The selected origin planet has no resources in inventory. Do you want to select another origin planet?");
                    if (!$changeOrigin) {
                        info("Operation cancelled.");
                        return;
                    }
                }

            } while (!$availableResources);

            $tradeRoute->origin_id = $originPlanetId;

            $resourceId = search(
                label: 'Select the new resource from this planet',
                placeholder: 'E.g. Iron',
                options: function (string $value) use ($originPlanetId) {
                    return strlen($value) > 0
                        ? Inventory::where('planet_id', $originPlanetId)
                            ->whereHas('resource', function ($query) use ($value) {
                                $query->where('name', 'like', "%{$value}%");
                            })
                            ->pluck('resource_id', 'resource_id')
                            ->mapWithKeys(fn($id) => [$id => Resource::find($id)->name])
                            ->toArray()
                        : [];
                }
            );

            if (!$resourceId) {
                error('No resource selected. Operation cancelled.');
                return;
            }

            $tradeRoute->resource_id = $resourceId;
            $fieldsToUpdate[] = 'quantity';
        }

        if (in_array('destination_id', $fieldsToUpdate)) {
            $newDestinationId = select(
                'Select the new destination planet:',
                Planet::pluck('name', 'id')->except($originPlanetId)->toArray()
            );

            if (!$newDestinationId) {
                error('No destination planet selected. Operation cancelled.');
                return;
            }

            $tradeRoute->destination_id = $newDestinationId;
        }

        if (in_array('quantity', $fieldsToUpdate)) {
            $quantity = text('Enter the new quantity of the resource to trade:');
            if (!is_numeric($quantity) || $quantity <= 0) {
                error("Quantity must be a positive number.");
                return;
            }

            $resourceInventory = Inventory::where('planet_id', $tradeRoute->origin_id)
                ->where('resource_id', $tradeRoute->resource_id)
                ->first();

            if (!$resourceInventory) {
                error("Resource not found in the inventory.");
                return;
            }

            if ($resourceInventory->quantity < $quantity) {
                error("Insufficient resource quantity in the inventory.");
                return;
            }

            $quantityDifference = $quantity - $tradeRoute->quantity;
            if ($quantityDifference > 0) {
                $resourceInventory->decrement('quantity', $quantityDifference);
            }
            if ($quantityDifference < 0) {
                $resourceInventory->increment('quantity', abs($quantityDifference));
            }

            $tradeRoute->quantity = $quantity;
        }

        if (in_array('travel_time', $fieldsToUpdate)) {
            $newTravelTime = text('Enter the new travel time (in hours):');
            if (!is_numeric($newTravelTime) || $newTravelTime <= 0) {
                error('Travel time must be a positive number.');
                return;
            }

            $tradeRoute->travel_time = $newTravelTime;
        }

        $tradeRoute->save();

        info("Trade Route '{$oldName}' has been updated successfully to:
            - Name:" . $tradeRoute->name . "
            - Origin: " . $tradeRoute->origin_id . "
            - Destination:" . $tradeRoute->destination_id . "
            - Resource: " . $tradeRoute->resource_id . "
            - Quantity: " . $tradeRoute->quantity . "
            - Travel Time: " . $tradeRoute->travel_time . "hours");

        $this->handle();
    }

    public function destroy()
    {
        $tradeRouteId = search(
            label: 'Search for the trade route to delete',
            placeholder: 'E.g. Saturn to Mars',
            options: fn(string $value) => strlen($value) > 0
            ? TradeRoute::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
            : [],
        );

        if (!$tradeRouteId) {
            error("No trade route selected. Operation cancelled.");
            return;
        }

        $tradeRoute = TradeRoute::find($tradeRouteId);
        $tradeRoute->delete();

        info("Trade route deleted successfully.");

        $this->handle();
    }
}
