<?php
// database/seeders/RolePermissionSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::create(['name' => 'news.create']);
        Permission::create(['name' => 'news.view']);
        Permission::create(['name' => 'news.update']);
        Permission::create(['name' => 'news.delete']);

        Permission::create(['name' => 'categories.manage']);

        Permission::create(['name' => 'admin.access']);

        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'news.create',
            'news.view',
            'news.update',
            'news.delete',
        ]);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
            ]
        );
        $admin->assignRole('admin');

        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'password' => bcrypt('password'),
            ]
        );
        $user->assignRole('user');
    }
}
