<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Création d'un utilisateur admin
        $user = User::create([
            'FirstName' => 'Saliou',
            'LastName' => 'TALLA',
            'Adress' => 'Dakar',
            'Phone' => '123456789',
            'email' => 'salioutalla@gmail.com',
            'password' => Hash::make('adminpassword'),
        ]);

        // Assigner le rôle admin
        $user->assignRole('Admin');
    }
}
