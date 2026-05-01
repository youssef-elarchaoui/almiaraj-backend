<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            // Voyages
            [
                'nomServ' => 'Aventure au Maroc',
                'description' => 'Découvrez les merveilles du Maroc - Circuit de 7 jours',
                'prix' => 2500,
                'type' => 'voyage',
                'image' => null,
                'rating' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nomServ' => 'Paris Romantique',
                'description' => 'Séjour romantique à Paris - 5 jours',
                'prix' => 1800,
                'type' => 'voyage',
                'image' => null,
                'rating' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Hotels
            [
                'nomServ' => 'Hotel Marrakech Palace',
                'description' => 'Luxueux hôtel 5 étoiles à Marrakech',
                'prix' => 800,
                'type' => 'hotel',
                'image' => null,
                'rating' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nomServ' => 'Riad Casablanca',
                'description' => 'Riad traditionnel au cœur de Casablanca',
                'prix' => 600,
                'type' => 'hotel',
                'image' => null,
                'rating' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Billets
            [
                'nomServ' => 'Vol Casablanca - Paris',
                'description' => 'Vol direct Casablanca - Paris',
                'prix' => 3500,
                'type' => 'billet',
                'image' => null,
                'rating' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nomServ' => 'Vol Marrakech - Dubai',
                'description' => 'Vol Marrakech - Dubai avec escale',
                'prix' => 4500,
                'type' => 'billet',
                'image' => null,
                'rating' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Hajj/Omra
            [
                'nomServ' => 'Hajj 2025',
                'description' => 'Pèlerinage à La Mecque - Hajj 2025',
                'prix' => 45000,
                'type' => 'hajjOmra',
                'image' => null,
                'rating' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nomServ' => 'Omra Ramadan',
                'description' => 'Omra pendant le mois de Ramadan',
                'prix' => 25000,
                'type' => 'hajjOmra',
                'image' => null,
                'rating' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        DB::table('services')->insert($services);
        
        $this->command->info('Created ' . count($services) . ' services successfully!');
    }
}