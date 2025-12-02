<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production environment.
     * Creates 3 admin users for client deployment.
     */
    public function run(): void
    {
        User::insert([
            [
                "name" => "Bintang Jaya 1",
                "username" => "admin1",
                "password" => bcrypt("bintangjaya1"),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Bintang Jaya 2",
                "username" => "admin2",
                "password" => bcrypt("bintangjaya2"),
                "created_at" => now(),
                "updated_at" => now(),
            ],
            [
                "name" => "Bintang Jaya 3",
                "username" => "admin3",
                "password" => bcrypt("bintangjaya3"),
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
