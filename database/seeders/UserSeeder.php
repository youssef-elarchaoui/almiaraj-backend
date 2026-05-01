<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'id' => 1,
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@email.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'name' => 'Karim Mohamed',
                'email' => 'karim.mohamed@email.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'name' => 'John Smith',
                'email' => 'john.smith@email.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'name' => 'Marie Bernard',
                'email' => 'marie.bernard@email.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'name' => 'Yasmine Tazi',
                'email' => 'yasmine.tazi@email.com',
                'password' => Hash::make('password123'),
                'role' => 'user',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['id' => $user['id']],
                $user
            );
        }
        
        $this->command->info('Created ' . count($users) . ' client users successfully!');
    }
}