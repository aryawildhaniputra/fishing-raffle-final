<?php

namespace Database\Seeders;

use App\Models\Participant;
use App\Models\ParticipantGroup;
use App\Support\Enums\ParticipantGroupRaffleStatusEnum;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParticipantGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = [
            'Gondanglegi', 'Turen', 'Kepanjen', 'Lawang', 'Singosari', 'Batu', 
            'Malang', 'Surabaya', 'Sidoarjo', 'Pasuruan', 'Probolinggo', 'Jember',
            'Kediri', 'Blitar', 'Tulungagung', 'Mojokerto', 'Gresik', 'Lamongan',
            'Bojonegoro', 'Tuban', 'Madiun', 'Ngawi', 'Magetan', 'Ponorogo'
        ];

        // Event 1: Random 1-2 orang
        $this->seedEvent(1, $cities, 1, 2);
        
        // Event 2: Random 1-3 orang
        $this->seedEvent(2, $cities, 1, 3);
        
        // Event 3: Random 1-4 orang
        $this->seedEvent(3, $cities, 1, 4);
        
        // Event 4: Random 1-5 orang
        $this->seedEvent(4, $cities, 1, 5);
        
        // Event 5: Random 1-5 orang
        $this->seedEvent(5, $cities, 1, 5);
    }

    /**
     * Seed participant groups for a specific event
     */
    private function seedEvent(int $eventId, array $cities, int $minMembers, int $maxMembers): void
    {
        $groups = [];
        $totalMembers = 0;
        $targetMembers = 222;
        $groupCounter = 1;

        while ($totalMembers < $targetMembers) {
            // Hitung sisa member yang dibutuhkan
            $remainingMembers = $targetMembers - $totalMembers;
            
            // Tentukan jumlah member untuk grup ini
            if ($remainingMembers < $minMembers) {
                break; // Stop jika sisa kurang dari minimum member
            } elseif ($remainingMembers < $maxMembers) {
                $members = $remainingMembers;
            } else {
                // Random antara min dan max
                $members = rand($minMembers, $maxMembers);
            }

            $groups[] = [
                "name" => $cities[array_rand($cities)] . ' ' . $groupCounter . '-' . $members,  // Format: "Lawang 1-2", "Singosari 5-3", etc.
                "phone_num" => '08' . rand(10, 99) . rand(10000000, 99999999),
                "event_id" => $eventId,
                "status" => "paid",
                "total_member" => $members,
                "raffle_status" => ParticipantGroupRaffleStatusEnum::NOT_YET->value,
                "created_at" => now(),
                "updated_at" => now(),
            ];

            $totalMembers += $members;
            $groupCounter++;

            // Insert per 100 data untuk efisiensi
            if (count($groups) >= 100) {
                ParticipantGroup::insert($groups);
                $groups = [];
            }
        }

        // Insert sisa data
        if (!empty($groups)) {
            ParticipantGroup::insert($groups);
        }
    }
}