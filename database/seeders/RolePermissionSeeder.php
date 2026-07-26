<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Seed default permissions, roles, and an initial super admin user.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete',
            'roles.view', 'roles.create', 'roles.edit', 'roles.delete',
            'customers.view', 'customers.create', 'customers.edit', 'customers.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            'milk_collections.view', 'milk_collections.create',
            'customer_orders.view', 'customer_orders.create', 'customer_orders.edit', 'customer_orders.delete',
            'customer_payments.view', 'customer_payments.create', 'customer_payments.edit', 'customer_payments.delete',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $superAdmin = Role::findOrCreate('Super Admin');
        $superAdmin->update(['description' => 'Full access to every module and setting.']);
        $superAdmin->syncPermissions($permissions);

        $admin = Role::findOrCreate('Admin');
        $admin->update(['description' => 'Manage users and view roles.']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'roles.view',
            'customers.view', 'customers.create', 'customers.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'milk_collections.view', 'milk_collections.create',
            'customer_orders.view', 'customer_orders.create', 'customer_orders.edit',
            'customer_payments.view', 'customer_payments.create', 'customer_payments.edit',
        ]);

        $user = User::firstOrCreate(
            ['email' => 'admin@dairyshop.test'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );

        $user->assignRole($superAdmin);
    }
}
