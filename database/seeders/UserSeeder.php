<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Адміністратор',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Головний адміністратор системи',
        ]);

        User::create([
            'name' => 'Олександр Коваленко',
            'email' => 'editor@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Редактор новинного порталу',
        ]);

        User::create([
            'name' => 'Марія Петренко',
            'email' => 'journalist@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Журналіст, пише про технології та інновації',
        ]);

        User::create([
            'name' => 'Іван Шевченко',
            'email' => 'ivan@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Автор статей про спорт',
        ]);

        User::create([
            'name' => 'Ольга Мельник',
            'email' => 'olga@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'avatar' => null,
            'bio' => 'Спеціаліст з культурних подій',
        ]);
    }
}
