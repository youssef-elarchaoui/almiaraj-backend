<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReservationSeeder extends Seeder
{
    public function run(): void
    {
        // First, make sure we have clients and services
        $clients = DB::table('clients')->pluck('id')->toArray();
        $services = DB::table('services')->pluck('id')->toArray();
        
        if (empty($clients)) {
            $this->command->error('No clients found. Please run ClientSeeder first.');
            return;
        }
        
        if (empty($services)) {
            $this->command->error('No services found. Please add services first.');
            return;
        }
        
        $statuses = ['pending', 'confirmed', 'cancelled'];
        $paymentStatuses = ['unpaid', 'paid', 'refunded'];
        
        $reservations = [];
        
        // Create 20 sample reservations
        for ($i = 1; $i <= 20; $i++) {
            $clientId = $clients[array_rand($clients)];
            $serviceId = $services[array_rand($services)];
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
            $nbPers = rand(1, 6);
            $prixUnitaire = rand(500, 5000);
            $prixTotal = $prixUnitaire * $nbPers;
            
            $createdAt = now()->subDays(rand(1, 90))->format('Y-m-d H:i:s');
            
            $reservations[] = [
                'service_id' => $serviceId,
                'client_id' => $clientId,
                'nbPers' => $nbPers,
                'prixUnitaire' => $prixUnitaire,
                'prixTotal' => $prixTotal,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'voucher_generated' => rand(0, 1),
                'reference' => 'RES-' . strtoupper(Str::random(8)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ];
        }
        
        // Insert in chunks to avoid memory issues
        foreach (array_chunk($reservations, 10) as $chunk) {
            DB::table('reservations')->insert($chunk);
        }
        
        $this->command->info('Created ' . count($reservations) . ' reservations successfully!');
    }
}