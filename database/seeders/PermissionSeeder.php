<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PermissionSeeder extends Seeder
{

    public function run()
    {

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard_access',
            'inventory_access',
            'inventory_create',
            'inventory_edit',
            'inventory_delete',
            'planet_access',
            'planet_create',
            'planet_edit',
            'planet_delete',
            'resource_access',
            'resource_create',
            'resource_edit',
            'resource_delete',
            'trade_route_access',
            'trade_route_create',
            'trade_route_edit',
            'trade_route_delete',
            'starship_access',
            'starship_create',
            'starship_edit',
            'starship_delete',
            'agreement_access',
            'agreement_create',
            'agreement_edit',
            'agreement_delete',
            'user_access',
            'user_create',
            'user_edit',
            'user_delete',

        ];

        foreach ($permissions as $permission) {
            if (! Permission::where('name', $permission)->exists()) {
                Permission::create([
                    'name' => $permission,
                ]);
            }
        }

        // dont change the order of the roles !!!

        $admin = Role::firstOrCreate(['name' => 'Admin']);
        $admin->givePermissionTo([]);

        $trader = Role::firstOrCreate(['name' => 'Trader']);
        $trader->givePermissionTo([]);

        $manager = Role::firstOrCreate(['name' => 'FleetManager']);
        $manager->givePermissionTo([]);

    }
}
