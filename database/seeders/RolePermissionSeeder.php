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
        ]);

        $user = User::firstOrCreate(
            ['email' => 'admin@dairyshop.test'],
            ['name' => 'Super Admin', 'password' => bcrypt('password')]
        );

        $user->assignRole($superAdmin);
    }
}
