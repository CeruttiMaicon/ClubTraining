<?php

namespace Database\Seeders\Tenants;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Criando e-mails admin e suporte
         */
        $usersDefault = [
            [
                'id' => 1,
                'name' => 'Administrador',
                'email' => env('MAIL_FROM_ADMIN'),
            ],
            [
                'id' => 2,
                'name' => 'Suporte',
                'email' => env('MAIL_FROM_ADDRESS'),
            ],
        ];

        /*
         * Criando e-mails para testes
         */
        if (env('APP_DEBUG')) {
            $usersDefault[] = [
                'id' => 3,
                'name' => 'Usuário Teste Técnico',
                'email' => env('MAIL_FROM_TEST_TECHNICIAN'),
            ];

            $usersDefault[] = [
                'id' => 4,
                'name' => 'Usuário Teste Jogador',
                'email' => env('MAIL_FROM_TEST_PLAYER'),
            ];

            $usersDefault[] = [
                'id' => 5,
                'name' => 'Usuário Sem Permissao',
                'email' => env('MAIL_FROM_NO_PERMISSION'),
            ];
        }

        foreach ($usersDefault as $user) {
            $user = User::updateOrCreate(
                ['id' => $user['id']],
                [
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]
            );

            $user->information()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'cpf' => '000000000' . $user->id,
                    'phone' => '00000000000' . $user->id,
                    'rg' => '000000000' . $user->id,
                    'birth_date' => '1998-01-06',
                ]
            );
        }
    }
}
