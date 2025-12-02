<?php

namespace Database\Seeders;

use App\Models\Stall;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stall::create([
            "total_stall" => 211,
            "created_at" => now(),
            "updated_at" => now(),
        ]);
    }
}
