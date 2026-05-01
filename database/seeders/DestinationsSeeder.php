<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class DestinationsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        Schema::disableForeignKeyConstraints();
        DB::table('destinations')->truncate();
        Schema::enableForeignKeyConstraints();

        $destinations = [
            // Afrique
            [
                'pays' => 'Maroc',
                'ville' => 'Casablanca',
                'continente' => 'Afrique',
                'en_vedette' => true,
                'description' => 'Le Maroc est un pays d\'Afrique du Nord avec des plages, des montagnes et des déserts. Connu pour sa cuisine riche, ses marchés animés et son architecture islamique.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Maroc',
                'ville' => 'Marrakech',
                'continente' => 'Afrique',
                'en_vedette' => true,
                'description' => 'Marrakech, la ville rouge, est célèbre pour ses souks animés, la place Jemaa el-Fna et ses magnifiques jardins.',
                'image' => 'img2.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Maroc',
                'ville' => 'Fès',
                'continente' => 'Afrique',
                'en_vedette' => false,
                'description' => 'Fès est la capitale spirituelle du Maroc, connue pour sa médina médiévale et ses tanneries traditionnelles.',
                'image' => 'img3.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Égypte',
                'ville' => 'Le Caire',
                'continente' => 'Afrique',
                'en_vedette' => true,
                'description' => 'L\'Égypte est célèbre pour ses pyramides anciennes, ses temples et la mer Rouge. Une terre de mystères et d\'aventures.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Égypte',
                'ville' => 'Louxor',
                'continente' => 'Afrique',
                'en_vedette' => false,
                'description' => 'Louxor est souvent appelé le plus grand musée à ciel ouvert du monde, avec ses temples et tombeaux pharaoniques.',
                'image' => 'img2.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Tunisie',
                'ville' => 'Tunis',
                'continente' => 'Afrique',
                'en_vedette' => false,
                'description' => 'La Tunisie offre des plages magnifiques, des sites archéologiques romains et un désert impressionnant.',
                'image' => 'img3.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Tunisie',
                'ville' => 'Djerba',
                'continente' => 'Afrique',
                'en_vedette' => false,
                'description' => 'Djerba est une île paradisiaque connue pour ses plages de sable fin et son ambiance détendue.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Europe
            [
                'pays' => 'France',
                'ville' => 'Paris',
                'continente' => 'Europe',
                'en_vedette' => true,
                'description' => 'La France est une destination de rêve avec ses monuments emblématiques, sa gastronomie raffinée et ses paysages variés.',
                'image' => 'img2.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'France',
                'ville' => 'Nice',
                'continente' => 'Europe',
                'en_vedette' => false,
                'description' => 'Nice est la perle de la Côte d\'Azur, célèbre pour sa Promenade des Anglais et son climat ensoleillé.',
                'image' => 'img3.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Espagne',
                'ville' => 'Barcelone',
                'continente' => 'Europe',
                'en_vedette' => true,
                'description' => 'L\'Espagne offre un mélange unique de culture, de plages et de fêtes traditionnelles.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Espagne',
                'ville' => 'Madrid',
                'continente' => 'Europe',
                'en_vedette' => false,
                'description' => 'Madrid, la capitale espagnole, est connue pour ses musées d\'art de renommée mondiale et sa vie nocturne animée.',
                'image' => 'img2.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Italie',
                'ville' => 'Rome',
                'continente' => 'Europe',
                'en_vedette' => true,
                'description' => 'L\'Italie est réputée pour son art, sa culture et sa cuisine. Des ruines romaines aux canaux de Venise.',
                'image' => 'img3.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Italie',
                'ville' => 'Venise',
                'continente' => 'Europe',
                'en_vedette' => false,
                'description' => 'Venise, la ville des canaux, est unique au monde avec ses gondoles et son architecture romantique.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Turquie',
                'ville' => 'Istanbul',
                'continente' => 'Europe',
                'en_vedette' => true,
                'description' => 'La Turquie est un pays transcontinental qui relie l\'Europe et l\'Asie. Célèbre pour ses mosquées historiques.',
                'image' => 'img2.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Turquie',
                'ville' => 'Cappadoce',
                'continente' => 'Europe',
                'en_vedette' => false,
                'description' => 'La Cappadoce est célèbre pour ses cheminées de fées et ses vols en montgolfière au-dessus de paysages lunaires.',
                'image' => 'img3.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Grèce',
                'ville' => 'Santorin',
                'continente' => 'Europe',
                'en_vedette' => true,
                'description' => 'La Grèce est connue pour ses îles blanches et bleues, son histoire antique et ses paysages à couper le souffle.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Asie
            [
                'pays' => 'Thaïlande',
                'ville' => 'Bangkok',
                'continente' => 'Asie',
                'en_vedette' => true,
                'description' => 'La Thaïlande est un paradis tropical avec des temples dorés, des plages paradisiaques et une cuisine épicée.',
                'image' => 'img2.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Thaïlande',
                'ville' => 'Phuket',
                'continente' => 'Asie',
                'en_vedette' => false,
                'description' => 'Phuket est la plus grande île de Thaïlande, célèbre pour ses plages magnifiques et sa vie nocturne animée.',
                'image' => 'img3.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Japon',
                'ville' => 'Tokyo',
                'continente' => 'Asie',
                'en_vedette' => true,
                'description' => 'Le Japon est un pays fascinant mêlant traditions ancestrales et technologie futuriste.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'Japon',
                'ville' => 'Kyoto',
                'continente' => 'Asie',
                'en_vedette' => false,
                'description' => 'Kyoto est l\'ancienne capitale impériale du Japon, connue pour ses temples, jardins et geishas.',
                'image' => 'img2.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Amériques
            [
                'pays' => 'États-Unis',
                'ville' => 'New York',
                'continente' => 'Amérique',
                'en_vedette' => true,
                'description' => 'Les États-Unis offrent une diversité incroyable de paysages et de cultures.',
                'image' => 'img3.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pays' => 'États-Unis',
                'ville' => 'Los Angeles',
                'continente' => 'Amérique',
                'en_vedette' => false,
                'description' => 'Los Angeles est la capitale du divertissement, célèbre pour Hollywood et ses plages.',
                'image' => 'img1.jpg',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($destinations as $destination) {
            DB::table('destinations')->insert($destination);
        }
    }
}