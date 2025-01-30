<?php

namespace App\Console\Commands;

use App\Models\Starship;
use App\Models\TradeRoute;
use App\Enums\StarshipStatus;
use Illuminate\Console\Command;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\table;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\multiselect;

class ManageStarships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'starships';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Starships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $function = select(
            label: 'What would you like to do?',
            options: ['Add Starship', 'Show Starships', 'Update Starship', 'Delete Starship', 'Exit'],
            required: true
        );

        match ($function) {
            'Add Starship' => $this->create(),
            'Show Starships' => $this->index(),
            'Delete Starship' => $this->destroy(),
            'Update Starship' => $this->update(),
            'Exit' => exit(),
        };

    }


    public function index()
    {
        $starships = Starship::all(['id', 'name', 'cargo_capacity', 'status'])->toArray();

        if (empty($starships)) {
            info('No starships found.');
            return;
        }

        table(['ID', 'Name', 'Cargo Capacity', 'Status'], $starships);

        $this->handle();
    }

    public function create()
    {
        do {
            $name = text('What is the name of the starship?');

            // Validate unique starship name
            if (Starship::where('name', $name)->exists()) {
                error("The starship name '{$name}' already exists. Please choose a different name.");
            }

        } while (Starship::where('name', $name)->exists());

        // Get cargo capacity
        $cargo_capacity = text('Enter the cargo capacity of the starship (in tons):');
        // Ensure the cargo capacity is a positive integer
        if (!is_numeric($cargo_capacity) || $cargo_capacity <= 0) {
            error("Cargo capacity must be a positive number.");
            return;
        }


        // Get status
        $status = select('Select the status of the starship:', [
            StarshipStatus::IDLE->value => 'Idle',
            StarshipStatus::IN_TRANSIT->value => 'In Transit',
            StarshipStatus::UNDER_MAINTENANCE->value => 'Under Maintenance',
        ]);

        $assignedRouteId = search(
            label: 'Search for the trade route to assign',
            placeholder: 'E.g. Saturn',
            options: fn(string $value) => strlen($value) > 0
            ? TradeRoute::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
            : TradeRoute::pluck('name', 'id')->all()
        );


        if (!$assignedRouteId) {
            error('No route selected. Operation cancelled.');
            return;
        }

        $starship = Starship::create([
            'name' => $name,
            'cargo_capacity' => $cargo_capacity,
            'status' => $status,
            'assigned_route_id' => $assignedRouteId,
        ]);

        info("Starship '{$name}' created successfully with cargo capacity of {$cargo_capacity} tons, and status '{$status}'.");

        $this->handle();
    }




    public function update()
    {
        $starshipId = search(
            label: 'Search for the starship to update',
            placeholder: 'E.g. Saturn',
            options: fn(string $value) => strlen($value) > 0
            ? Starship::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
            : Starship::pluck('name', 'id')->all()
        );

        if (!$starshipId) {
            error("No starship selected. Operation cancelled.");
            return;
        }

        $starship = Starship::find($starshipId);
        $old_name = $starship->name;

        $fieldsToUpdate = multiselect(
            label: 'Select fields to update',
            options: [
                'name' => 'Name',
                'cargo_capacity' => 'Cargo Capacity',
                'status' => 'Status',
                'assigned_route_id' => 'Assigned Route',
            ]
        );

        if (empty($fieldsToUpdate)) {
            error("No fields selected. Operation cancelled.");
            return;
        }

        $newName = $starship->name;
        $newCargoCapacity = $starship->cargo_capacity;
        $newStatus = $starship->status;
        $newAssignedRouteId = $starship->assigned_route_id;

        if (in_array('name', $fieldsToUpdate)) {
            do {
                $newName = text('Enter the new name for the starship:');

                if (Starship::where('name', $newName)->exists()) {
                    error("The starship name '{$newName}' already exists. Please choose a different name.");
                }

            } while (Starship::where('name', $newName)->exists()); // Ensure uniqueness
        }

        if (in_array('cargo_capacity', $fieldsToUpdate)) {
            $newCargoCapacity = text('Enter the new cargo capacity for the starship (in tons):');
            if (!is_numeric($newCargoCapacity) || $newCargoCapacity <= 0) {
                error("Cargo capacity must be a positive number.");
                return;
            }
        }


        if (in_array('status', $fieldsToUpdate)) {
            $newStatus = select('Select the new status for the starship:', [
                StarshipStatus::IDLE->value => 'Idle',
                StarshipStatus::IN_TRANSIT->value => 'In Transit',
                StarshipStatus::UNDER_MAINTENANCE->value => 'Under Maintenance',
            ]);
        }

        if (in_array('assigned_route_id', $fieldsToUpdate)) {
            $newAssignedRouteId = search(
                label: 'Search the new trade route to assign',
                placeholder: 'E.g. Saturn',
                options: fn(string $value) => strlen($value) > 0
                ? TradeRoute::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : TradeRoute::pluck('name', 'id')->all()
            );

            if (!$newAssignedRouteId) {
                error('No route selected. Operation cancelled.');
                return;
            }
        }

        // Perform the update
        $starship->update([
            'name' => $newName,
            'cargo_capacity' => $newCargoCapacity,
            'status' => $newStatus,
            'assigned_route_id' => $newAssignedRouteId,
        ]);

        info("Starship '{$old_name}' has been updated successfully to:
            - Name: '{$newName}'
        - Cargo Capacity: {$newCargoCapacity} tons
            - Status: " . $newStatus->value . "
            - Assigned Route ID: {$newAssignedRouteId}");

        $this->handle();
    }


    public function destroy()
    {
        $starship = search(
            label: 'Search for the starship to delete',
            placeholder: 'E.g. Saturn',
            options: fn(string $value) => strlen($value) > 0
            ? Starship::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
            : Starship::pluck('name', 'id')->all()
        );

        if (!$starship) {
            error("No starship selected. Operation cancelled.");
            return;
        }

        Starship::find($starship)->delete();

        info("starship deleted successfully.");
        $this->handle();

    }

}
