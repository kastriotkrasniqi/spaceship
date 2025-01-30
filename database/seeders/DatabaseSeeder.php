<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Planet;
use App\Models\Resource;
use App\Models\Starship;
use App\Models\Inventory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Planet::factory(5)->create();
        Resource::factory(5)->create();
        Inventory::factory(5)->create();
        Starship::factory(5)->create();

        $this->call([
            PermissionSeeder::class,
        ]);
    }
}
