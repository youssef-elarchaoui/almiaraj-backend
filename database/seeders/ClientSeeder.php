<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $clients = [
            [
                'id' => 1,
                'nomCl' => 'Dupont',
                'prenomCl' => 'Jean',
                'email' => 'jean.dupont@email.com',
                'numTelCl' => '0612345678',
                'natCl' => 'France',
                'cin' => 'AB123456',
                'passport' => 'P12345678',
                'dateInscription' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'nomCl' => 'Mohamed',
                'prenomCl' => 'Karim',
                'email' => 'karim.mohamed@email.com',
                'numTelCl' => '0678912345',
                'natCl' => 'Maroc',
                'cin' => 'CD789012',
                'passport' => 'M87654321',
                'dateInscription' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'nomCl' => 'Smith',
                'prenomCl' => 'John',
                'email' => 'john.smith@email.com',
                'numTelCl' => '0698765432',
                'natCl' => 'USA',
                'cin' => 'EF345678',
                'passport' => 'U12345678',
                'dateInscription' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'nomCl' => 'Bernard',
                'prenomCl' => 'Marie',
                'email' => 'marie.bernard@email.com',
                'numTelCl' => '0654321098',
                'natCl' => 'France',
                'cin' => 'GH901234',
                'passport' => 'F87654321',
                'dateInscription' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 5,
                'nomCl' => 'Tazi',
                'prenomCl' => 'Yasmine',
                'email' => 'yasmine.tazi@email.com',
                'numTelCl' => '0666666666',
                'natCl' => 'Maroc',
                'cin' => 'IJ567890',
                'passport' => 'M12345678',
                'dateInscription' => now()->format('Y-m-d'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($clients as $client) {
            DB::table('clients')->updateOrInsert(
                ['id' => $client['id']],
                $client
            );
        }
        
        $this->command->info('Created ' . count($clients) . ' clients successfully!');
    }
}