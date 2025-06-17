<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name1' => 'Juan',
            'name2'=> 'David',
            'surname1' => 'plazas',
            'surname2' => 'hernandez',
            'email' => 'test@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('juan1234'),
            'rol' => 'Cajero',
        ]);

        User::factory()->create([
            'name1' => 'Juan',
            'name2'=> 'Alejandro',
            'surname1' => 'Muñoz',
            'surname2' => 'Devia',
            'email' => 'juanmunoz@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('juanmunoz1234'),
            'rol' => 'Administrador',
        ]);

        User::factory()->create([
            'name1' => 'Jaider',
            'name2'=> 'Daniel',
            'surname1' => 'Machado',
            'surname2' => 'Caleño',
            'email' => 'jaider@gmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('jaidermc12345'),
            'rol' => 'Cajero',
        ]);

        User::factory()->create([
            'name1' => 'Camilo',
            'name2'=> 'Andres',
            'surname1' => 'Paredes',
            'surname2' => 'Castellanos',
            'email' => 'camilo@hotmail.com',
            'email_verified_at' => now(),
            'password' => Hash::make('camilo1@23'),
            'rol' => 'Panadero',
        ]);
    }
}
