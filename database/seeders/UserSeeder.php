<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Development seeder - commented out for production deployment
        User::insert([
            [
                "name" => "admin",
                "username" => "farhan12",
                "password" => bcrypt("adminpass"),
                "created_at" => now(),
                "updated_at" => now(),
            ],
        ]);
    }
}
