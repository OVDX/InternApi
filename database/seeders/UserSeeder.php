<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);

        $admin = User::create([
            'name' => 'Адміністратор',
            'email' => 'admin2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Головний адміністратор системи',
        ]);
        $admin->assignRole('admin');

        User::create([
            'name' => 'Олександр Коваленко',
            'email' => 'editor2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Редактор новинного порталу',
        ])->assignRole('user');

        User::create([
            'name' => 'Марія Петренко',
            'email' => 'journalist2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Журналіст, пише про технології та інновації',
        ])->assignRole('user');

        User::create([
            'name' => 'Іван Шевченко',
            'email' => 'ivan2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Автор статей про спорт',
        ])->assignRole('user');

        User::create([
            'name' => 'Ольга Мельник',
            'email' => 'olga2@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Спеціаліст з культурних подій',
        ])->assignRole('user');
    }
}
