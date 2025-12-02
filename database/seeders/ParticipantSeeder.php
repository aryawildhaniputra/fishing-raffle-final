<?php

namespace Database\Seeders;

use App\Models\Participant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Participant::insert([
        //     [
        //         "name" => "Gondanglegi_1",
        //         "event_id" => 1,
        //         "participant_groups_id" => 1,
        //         "stall_number" => 3,
        //         "created_at" => now(),
        //         "updated_at" => now(),
        //     ],
        //     [
        //         "name" => "Gondanglegi_2",
        //         "event_id" => 1,
        //         "participant_groups_id" => 1,
        //         "stall_number" => 4,
        //         "created_at" => now(),
        //         "updated_at" => now(),
        //     ],
        //     [
        //         "name" => "Gondanglegi_3",
        //         "event_id" => 1,
        //         "participant_groups_id" => 1,
        //         "stall_number" => 5,
        //         "created_at" => now(),
        //         "updated_at" => now(),
        //     ]
        // ]);
    }
}
