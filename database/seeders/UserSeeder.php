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
        // // Création d'un utilisateur admin
        // $user = User::create([
        //     'FirstName' => 'Saliou',
        //     'LastName' => 'TALLA',
        //     'Adress' => 'Dakar',
        //     'Phone' => '123456789',
        //     'email' => 'salioutalla@gmail.com',
        //     'password' => Hash::make('adminpassword'),
        // ]);

        // // Assigner le rôle admin
        // $user->assignRole('Admin');
         // Liste de données d'utilisateurs fictifs
         $users = [
            [
                'FirstName' => 'Aliou',
                'LastName' => 'Diallo',
                'Adress' => 'Dakar',
                'Phone' => '771234567',
                'email' => 'aliou.diallo@example.com',
                'password' => Hash::make('password1')
            ],
            [
                'FirstName' => 'Awa',
                'LastName' => 'Diop',
                'Adress' => 'Thies',
                'Phone' => '778765432',
                'email' => 'awa.diop@example.com',
                'password' => Hash::make('password2')
            ],
            [
                'FirstName' => 'Moussa',
                'LastName' => 'Sow',
                'Adress' => 'Kaolack',
                'Phone' => '770123456',
                'email' => 'moussa.sow@example.com',
                'password' => Hash::make('password3')
            ],
            [
                'FirstName' => 'Fatou',
                'LastName' => 'Ndiaye',
                'Adress' => 'Saint-Louis',
                'Phone' => '779876543',
                'email' => 'fatou.ndiaye@example.com',
                'password' => Hash::make('password4')
            ],
            [
                'FirstName' => 'Boubacar',
                'LastName' => 'Ba',
                'Adress' => 'Ziguinchor',
                'Phone' => '778654321',
                'email' => 'boubacar.ba@example.com',
                'password' => Hash::make('password5')
            ],
        ];

        //  Créer les utilisateurs et leur assigner le rôle SimpleUser
         foreach ($users as $userData) {
            $user = User::create($userData);
            $user->assignRole('SimpleUser'); // Assigner le rôle SimpleUser
        }

    }
}
