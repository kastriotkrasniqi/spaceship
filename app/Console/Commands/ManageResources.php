<?php

namespace App\Console\Commands;

use App\Models\Resource;
use Illuminate\Console\Command;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\info;
use function Laravel\Prompts\error;
use function Laravel\Prompts\search;
use function Laravel\Prompts\table;

class ManageResources extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Resources';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $function = select(
            label: 'What would you like to do?',
            options: ['Add Resource', 'Show Resources', 'Delete Resource', 'Update Resource','Exit'],
            required: true
        );

        match ($function) {
            'Add Resource' => $this->create(),
            'Show Resources' => $this->index(),
            'Delete Resource' => $this->destroy(),
            'Update Resource' => $this->update(),
            'Exit' => exit(),
        };

    }


    public function index()
    {
        $resources = Resource::all(['id', 'name'])->toArray();

        if (empty($resources)) {
            info('No resources found.');
            return;
        }

        table(['ID', 'Name'], $resources);
    }

    public function create()
    {
        do {
            $name = text('What is the name of the resource?');

            if (Resource::where('name', $name)->exists()) {
                error("The resource name '{$name}' already exists. Please choose a different name.");
            }

        } while (Resource::where('name', $name)->exists());

        $resource = Resource::create([
            'name' => $name,
        ]);

        info("{$name} created successfully.");

        $this->handle();
    }




    public function update()
    {
        $resourceId = search(
            label: 'Search for the resource to update',
            placeholder: 'E.g. Saturn',
            options: fn (string $value) => strlen($value) > 0
                ? Resource::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : [],
        );

        if (!$resourceId) {
            error("No resource selected. Operation cancelled.");
            return;
        }

        $resource = Resource::find($resourceId);
        $old_name = $resource->name;

        do {
            $newName = text('Enter the new name for the resource:');

            if (Resource::where('name', $newName)->exists()) {
                error("The resource name {$newName} already exists. Please choose a different name.");
            }

        } while (Resource::where('name', $newName)->exists()); // Ensure uniqueness

        $resource->update(['name' => $newName]);

        info("Resource '{$old_name}' has been renamed to '{$newName}' successfully.");

        $this->handle();
    }


    public function destroy()
    {
        $resource = search(
            label: 'Search for the resource to delete',
            placeholder: 'E.g. Saturn',
            options: fn (string $value) => strlen($value) > 0
                ? Resource::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : [],
        );

        if (!$resource) {
            error("No resource selected. Operation cancelled.");
            return;
        }

        Resource::find($resource)->delete();

        info("Resource deleted successfully.");
        $this->handle();
    }




}
