<?php

namespace App\Console\Commands;

use App\Models\Inventory;
use App\Models\Planet;
use App\Models\Resource;
use Illuminate\Console\Command;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\select;
use function Laravel\Prompts\error;
use function Laravel\Prompts\search;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\table;

class ManageInventories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventories';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Inventories of resources on planets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $function = select(
            label: 'What would you like to do?',
            options: ['Add Inventory', 'Show Inventories', 'Update Inventory', 'Delete Inventory','Exit'],
            required: true
        );

        match ($function) {
            'Add Inventory' => $this->create(),
            'Show Inventories' => $this->index(),
            'Update Inventory' => $this->update(),
            'Delete Inventory' => $this->destroy(),
            'Exit' => exit(),
        };
    }

    public function index()
    {
        $inventories = Inventory::with(['planet', 'resource'])->get(['id', 'planet_id', 'resource_id', 'quantity', 'price'])->toArray();

        if (empty($inventories)) {
            info('No inventories found.');
            return;
        }

        table(['ID', 'Planet', 'Resource', 'Quantity', 'Price'], $inventories);

        $this->handle();
    }

    public function create()
    {
        $planetId = search(
            label: 'Search for the planet',
            placeholder: 'E.g. Earth',
            options: fn(string $value) => strlen($value) > 0
                ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : [],
        );

        if (!$planetId) {
            error('No planet selected. Operation cancelled.');
            return;
        }

        $resourceId = search(
            label: 'Search for the resource',
            placeholder: 'E.g. Water',
            options: fn(string $value) => strlen($value) > 0
                ? Resource::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                : [],
        );

        if (!$resourceId) {
            error('No resource selected. Operation cancelled.');
            return;
        }

        $quantity = text('Enter the quantity of the resource:');
        if (!is_numeric($quantity) || $quantity <= 0) {
            error("Quantity must be a positive number.");
            return;
        }

        $price = text('Enter the price of the resource:');
        if (!is_numeric($price) || $price <= 0) {
            error("Price must be a positive number.");
            return;
        }

        // Create the inventory record
        Inventory::create([
            'planet_id' => $planetId,
            'resource_id' => $resourceId,
            'quantity' => $quantity,
            'price' => $price,
        ]);

        info("Inventory created successfully for planet ID {$planetId}, resource ID {$resourceId}, quantity {$quantity}, price {$price}.");

        $this->handle();
    }

    public function update()
    {
        $inventoryId = search(
            label: 'Search for the inventory to update',
            placeholder: 'E.g. 1',
            options: fn(string $value) => strlen($value) > 0
                ? Inventory::whereLike('id', "%{$value}%")->pluck('id', 'id')->all()
                : [],
        );

        if (!$inventoryId) {
            error("No inventory selected. Operation cancelled.");
            return;
        }

        $inventory = Inventory::find($inventoryId);

        $fieldsToUpdate = multiselect(
            label: 'Select fields to update',
            options: [
                'quantity' => 'Quantity',
                'price' => 'Price',
            ]
        );

        if (empty($fieldsToUpdate)) {
            error("No fields selected. Operation cancelled.");
            return;
        }

        $newQuantity = $inventory->quantity;
        $newPrice = $inventory->price;

        if (in_array('quantity', $fieldsToUpdate)) {
            $newQuantity = text('Enter the new quantity for the resource:');
            if (!is_numeric($newQuantity) || $newQuantity <= 0) {
                error("Quantity must be a positive number.");
                return;
            }
        }

        if (in_array('price', $fieldsToUpdate)) {
            $newPrice = text('Enter the new price for the resource:');
            if (!is_numeric($newPrice) || $newPrice <= 0) {
                error("Price must be a positive number.");
                return;
            }
        }

        $inventory->update([
            'quantity' => $newQuantity,
            'price' => $newPrice,
        ]);

        info("Inventory updated successfully:
            - Quantity: {$newQuantity}
            - Price: {$newPrice}");

        $this->handle();
    }

    public function destroy()
    {
        $inventoryId = search(
            label: 'Search for the inventory to delete',
            placeholder: 'E.g. 1',
            options: fn(string $value) => strlen($value) > 0
                ? Inventory::whereLike('id', "%{$value}%")->pluck('id', 'id')->all()
                : [],
        );

        if (!$inventoryId) {
            error("No inventory selected. Operation cancelled.");
            return;
        }

        Inventory::find($inventoryId)->delete();

        info("Inventory deleted successfully.");

        $this->handle();
    }
}
