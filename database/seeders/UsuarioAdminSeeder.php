<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'nome_Usuario' => 'Admin',
                'email' => 'admin@gmail.com',
                'tipo_Usuario' => 'admin',
                'nivel_Usuario' => 'admin',
                'funcao' => 'Administrador',
                'senha' => Hash::make('1234')
            ]
        );
    }
}

