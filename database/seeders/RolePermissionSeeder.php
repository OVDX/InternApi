<?php

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


        $newsPermissions = [
            'news.view',      // index, show (public + auth)
            'news.create',    // store
            'news.update',    // update
            'news.delete',    // destroy
            'news.toggle',    // toggle-status
        ];

        $categoryPermissions = [
            'categories.manage',  // create, update, delete
        ];

        $userPermissions = [
            'users.profile.update',  // profile update
            'users.avatar.manage',   // upload/delete avatar
            'users.admin.manage',    // admin/users (CRUD)
        ];

        $adminPermissions = ['admin.access'];

        $allPermissions = array_merge($newsPermissions, $categoryPermissions, $userPermissions, $adminPermissions);

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
        $userRole->syncPermissions([
            'news.view',
            'news.create',
            'news.update',
            'news.delete',
            'users.profile.update',
            'users.avatar.manage',
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(Permission::all());

        $admin = User::firstOrCreate(['email' => 'admin@example.com'], [
            'name' => 'Admin',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('admin');

        $user = User::firstOrCreate(['email' => 'user@example.com'], [
            'name' => 'User',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('user');
    }
}
