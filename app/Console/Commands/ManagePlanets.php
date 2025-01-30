<?php

namespace App\Console\Commands;

use App\Models\Planet;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\search;
use function Laravel\Prompts\table;

class ManagePlanets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'planets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Planets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $function = select(
            label: 'What would you like to do?',
            options: ['Add Planet', 'Show Planets', 'Delete Planet', 'Update Planet'],
            required: true
        );

        match ($function) {
            'Add Planet' => $this->create(),
            'Show Planets' => $this->index(),
            'Delete Planet' => $this->destroy(),
            'Update Planet' => $this->update(),
        };

    }


    public function index()
    {
        $planets = Planet::all(['id', 'name'])->toArray();

        if (empty($planets)) {
            info('No planets found.');
            return;
        }

        table(['ID', 'Name'], $planets);
    }

    public function create()
    {
        do {
            $name = text('What is the name of the planet?');

            if (Planet::where('name', $name)->exists()) {
                error("The planet name '{$name}' already exists. Please choose a different name.");
            }

        } while (Planet::where('name', $name)->exists());

        $planet = Planet::create([
            'name' => $name,
        ]);

        info("{$name} created successfully.");
    }




    public function update()
    {
        $planetId = search(
            label: 'Search for the planet to update',
            placeholder: 'E.g. Saturn',
            options: fn (string $value) => strlen($value) > 0
                ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : [],
        );

        if (!$planetId) {
            error("No planet selected. Operation cancelled.");
            return;
        }

        $planet = Planet::find($planetId);
        $old_name = $planet->name;

        do {
            $newName = text('Enter the new name for the planet:');

            if (Planet::where('name', $newName)->exists()) {
                error("The planet name {$newName} already exists. Please choose a different name.");
            }

        } while (Planet::where('name', $newName)->exists()); // Ensure uniqueness

        $planet->update(['name' => $newName]);

        info("Planet '{$old_name}' has been renamed to '{$newName}' successfully.");
    }


    public function destroy()
    {
        $planet = search(
            label: 'Search for the planet to delete',
            placeholder: 'E.g. Saturn',
            options: fn (string $value) => strlen($value) > 0
                ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : [],
        );

        if (!$planet) {
            error("No planet selected. Operation cancelled.");
            return;
        }

        Planet::find($planet)->delete();

        info("Planet deleted successfully.");
    }



}
