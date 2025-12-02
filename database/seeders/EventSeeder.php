<?php

namespace Database\Seeders;

use App\Models\Event;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Data manual yang sudah ada
        Event::insert([
            [
                "name" => "Event Test - Grup 1-2 Orang",
                "event_date" => now(),
                "price" => 250000,
                "total_registrant" => 222,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Event Test - Grup 1-3 Orang",
                "event_date" => now()->addDays(1),
                "price" => 250000,
                "total_registrant" => 222,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Event Test - Grup 1-4 Orang",
                "event_date" => now()->addDays(2),
                "price" => 250000,
                "total_registrant" => 222,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Event Test - Grup 1-5 Orang (A)",
                "event_date" => now()->addDays(3),
                "price" => 250000,
                "total_registrant" => 222,
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Event Test - Grup 1-5 Orang (B)",
                "event_date" => now()->addDays(4),
                "price" => 250000,
                "total_registrant" => 222,
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);

        // // Generate 200 data random untuk event mancing
        // $events = [];
        
        // $eventTypes = [
        //     'Lomba Mancing',
        //     'Turnamen Mancing',
        //     'Festival Mancing',
        //     'Kompetisi Mancing',
        //     'Lomba Strike',
        //     'Open Tournament',
        //     'Championship Mancing',
        //     'Mancing Bareng',
        //     'Gathering Pemancing',
        //     'Kompetisi Casting'
        // ];
        
        // $locations = [
        //     'Danau Toba',
        //     'Waduk Jatiluhur',
        //     'Pantai Anyer',
        //     'Teluk Jakarta',
        //     'Danau Maninjau',
        //     'Bendungan Karangkates',
        //     'Pantai Pangandaran',
        //     'Teluk Bone',
        //     'Danau Sentani',
        //     'Waduk Cirata',
        //     'Pantai Parangtritis',
        //     'Teluk Lampung',
        //     'Danau Tempe',
        //     'Waduk Kedungombo',
        //     'Pantai Bali'
        // ];
        
        // $fishTypes = [
        //     'Ikan Kakap',
        //     'Ikan Bawal',
        //     'Ikan Nila',
        //     'Ikan Lele',
        //     'Ikan Patin',
        //     'Ikan Gabus',
        //     'Ikan Tuna',
        //     'Ikan Marlin',
        //     'Ikan Tenggiri',
        //     'Ikan Mas'
        // ];
        
        // for ($i = 1; $i <= 200; $i++) {
        //     $eventType = $eventTypes[array_rand($eventTypes)];
        //     $location = $locations[array_rand($locations)];
        //     $fishType = $fishTypes[array_rand($fishTypes)];
            
        //     // Variasi nama event
        //     $nameVariations = [
        //         "{$eventType} {$fishType} {$location}",
        //         "{$eventType} {$location} - Target {$fishType}",
        //         "{$eventType} {$location} Season {$i}",
        //         "{$location} {$eventType} Championship",
        //     ];
            
        //     $events[] = [
        //         'name' => $nameVariations[array_rand($nameVariations)],
        //         'event_date' => now()->addDays(rand(5, 180)),
        //         'price' => rand(150000, 500000),
        //         'total_registrant' => rand(20, 300),
        //         'created_at' => now(),
        //         'updated_at' => now(),
        //     ];
        // }
        
        // // Insert 200 data random dalam batch
        // Event::insert($events);
    }
}