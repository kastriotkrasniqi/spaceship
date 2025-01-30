<?php

namespace App\Console\Commands;

use App\Models\Planet;
use App\Models\Resource;
use App\Models\Inventory;
use App\Models\TradeAgreement;
use Illuminate\Console\Command;
use function Laravel\Prompts\info;
use function Laravel\Prompts\text;
use function Laravel\Prompts\error;
use function Laravel\Prompts\table;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\confirm;

class ManageTradeAgreements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trade-agreements';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage Trade Agreements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $function = select(
            label: 'What would you like to do?',
            options: ['Add Trade Agreement', 'Show Trade Agreements', 'Update Trade Agreement', 'Delete Trade Agreement', 'Exit'],
            required: true
        );

        match ($function) {
            'Add Trade Agreement' => $this->create(),
            'Show Trade Agreements' => $this->index(),
            'Delete Trade Agreement' => $this->destroy(),
            'Update Trade Agreement' => $this->update(),
            'Exit' => exit(),
        };
    }

    public function index()
    {
        $agreements = TradeAgreement::with(['origin', 'destination', 'resource'])
            ->get(['id', 'origin_id', 'destination_id', 'resource_id', 'quantity', 'frequency', 'next_delivery'])
            ->map(function ($agreement) {
                return [
                    'id' => $agreement->id,
                    'origin' => $agreement->origin->name,
                    'destination' => $agreement->destination->name,
                    'resource' => $agreement->resource->name,
                    'quantity' => $agreement->quantity,
                    'frequency' => $agreement->frequency,
                    'next_delivery' => $agreement->next_delivery
                ];
            });

        $agreements = $agreements->toArray();

        if (empty($agreements)) {
            info('No trade agreements found.');
            return;
        }

        table(
            ['ID', 'Origin Planet', 'Destination Planet', 'Resource', 'Quantity', 'Frequency (Days)', 'Next Delivery'],
            $agreements
        );


    }


    public function create()
    {
        $originPlanetId = search(
            label: 'Search for the origin planet',
            placeholder: 'E.g. Earth',
            options: fn(string $value) => strlen($value) > 0
            ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
            : [],
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
                    ? Planet::where('name', 'LIKE', "%{$value}%")
                        ->pluck('name', 'id')
                        ->except($originPlanetId)
                        ->all()
                    : [];
            }
        );


        if (!$destinationPlanetId) {
            error('No destination planet selected. Operation cancelled.');
            return;
        }

        $resourceOptions = Inventory::where('planet_id', $originPlanetId)
            ->pluck('resource_id')->unique()
            ->mapWithKeys(function ($resourceId) {
                return [$resourceId => Resource::find($resourceId)->name];
            })->toArray();

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

        $frequency = text('Enter the frequency of the trade (in days):');
        if (!is_numeric($frequency) || $frequency <= 0) {
            error("Frequency must be a positive number.");
            return;
        }

        $nextDelivery = text('Enter the next delivery date (YYYY-MM-DD):');
        $nextDeliveryDate = \Carbon\Carbon::parse($nextDelivery);

        $tradeAgreement = TradeAgreement::create([
            'origin_id' => $originPlanetId,
            'destination_id' => $destinationPlanetId,
            'resource_id' => $resourceId,
            'quantity' => $quantity,
            'frequency' => $frequency,
            'next_delivery' => $nextDeliveryDate,
        ]);

        info("Trade agreement created successfully from planet ID {$originPlanetId} to planet ID {$destinationPlanetId} for resource ID {$resourceId}, quantity {$quantity}, frequency {$frequency} days, next delivery on {$nextDeliveryDate->toDateString()}.");

        info("Remaining quantity of resource '{$inventory->resource->name}': {$inventory->quantity}");

        $this->handle();
    }

    public function update()
    {
        $tradeAgreementId = search(
            label: 'Search for the trade agreement to update',
            placeholder: 'E.g. 1',
            options: fn(string $value) => strlen($value) > 0
            ? TradeAgreement::whereLike('id', "%{$value}%")->pluck('id', 'id')->all()
            : [],
        );

        if (!$tradeAgreementId) {
            error("Trade agreement not found.");
            return;
        }

        $tradeAgreement = TradeAgreement::find($tradeAgreementId);

        $fieldsToUpdate = multiselect(
            label: 'Select the fields you want to update',
            options: [
                'origin_id' => 'Origin Planet',
                'destination_id' => 'Destination Planet',
                'resource_id' => 'Resource',
                'quantity' => 'Quantity',
                'frequency' => 'Frequency',
                'next_delivery' => 'Next Delivery Date',
            ]
        );

        if (empty($fieldsToUpdate)) {
            info("No fields selected. Operation cancelled.");
            return;
        }

        if (in_array('origin_id', $fieldsToUpdate)) {
            do {
                $originPlanetId = search(
                    label: 'Search for the origin planet',
                    placeholder: 'E.g. Earth',
                    options: fn(string $value) => strlen($value) > 0
                    ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->all()
                    : [],
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

            $tradeAgreement->origin_id = $originPlanetId;

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

            $tradeAgreement->resource_id = $resourceId;
            $fieldsToUpdate[] = 'quantity';
        }

        if (in_array('destination_id', $fieldsToUpdate)) {
            $destinationPlanetId = search(
                label: 'Search for the destination planet',
                placeholder: 'E.g. Mars',
                options: function (string $value) use($tradeAgreement) {
                    return strlen($value) > 0
                        ? Planet::whereLike('name', "%{$value}%")->pluck('name', 'id')->except($tradeAgreement->origin_id)->all()
                        : [];
                }
            );

            if (!$destinationPlanetId) {
                error('No destination planet selected. Operation cancelled.');
                return;
            }

            $tradeAgreement->destination_id = $destinationPlanetId;
        }

        if (in_array('quantity', $fieldsToUpdate)) {
            $quantity = text('Enter the new quantity of the resource to trade:');
            if (!is_numeric($quantity) || $quantity <= 0) {
                error("Quantity must be a positive number.");
                return;
            }

            $resourceInventory = Inventory::where('planet_id', $tradeAgreement->origin_id)
                ->where('resource_id', $tradeAgreement->resource_id)
                ->first();

            if (!$resourceInventory) {
                error("Resource not found in the inventory.");
                return;
            }

            if ($resourceInventory->quantity < $quantity) {
                error("Insufficient resource quantity in the inventory.");
                return;
            }

            $tradeAgreement->quantity = $quantity;
        }

        if (in_array('frequency', $fieldsToUpdate)) {
            $frequency = text('Enter the new frequency (in days):');
            if (!is_numeric($frequency) || $frequency <= 0) {
                error("Frequency must be a positive number.");
                return;
            }

            $tradeAgreement->frequency = $frequency;
        }

        if (in_array('next_delivery', $fieldsToUpdate)) {
            $nextDelivery = text('Enter the new next delivery date (YYYY-MM-DD):');
            try {
                $tradeAgreement->next_delivery = \Carbon\Carbon::parse($nextDelivery);
            } catch (\Exception $e) {
                error("Invalid date format.");
                return;
            }
        }

        $tradeAgreement->save();

        info("Trade agreement updated successfully with the selected changes.");

        $this->handle();
    }


    public function destroy()
    {
        $agreementId = search(
            label: 'Search for the trade agreement to delete',
            placeholder: 'E.g. 1',
            options: fn(string $value) => strlen($value) > 0
            ? TradeAgreement::whereLike('id', "%{$value}%")->pluck('id', 'id')->all()
            : [],
        );

        if (!$agreementId) {
            error("No trade agreement selected. Operation cancelled.");
            return;
        }

        TradeAgreement::find($agreementId)->delete();

        info("Trade agreement deleted successfully.");

        $this->handle();
    }
}
