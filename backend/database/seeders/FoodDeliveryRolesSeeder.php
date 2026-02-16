<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FoodDeliveryRolesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['customer', 'restaurant_owner', 'driver', 'admin'] as $name) {
            Role::query()->firstOrCreate(
                ['name' => $name, 'guard_name' => 'web']
            );
        }

        $manageOrders = Permission::query()->firstOrCreate(
            ['name' => 'orders.manage_restaurant', 'guard_name' => 'web']
        );

        $walletManage = Permission::query()->firstOrCreate(
            ['name' => 'wallet.manage_restaurant', 'guard_name' => 'web']
        );

        Role::query()->firstOrCreate(['name' => 'restaurant_owner', 'guard_name' => 'web'])
            ->givePermissionTo([$manageOrders, $walletManage]);
    }
}
