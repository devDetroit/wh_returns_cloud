<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'complete_name' => 'Manuel Herrera',
            'username' => 'maherrera',
            'email' => 'maherrera@detroitaxle.com',
            'email_verified_at' => now(),
            'user_type' => 'viwer',
            'password' => bcrypt('maherrera'), // password
            'remember_token' => Str::random(10),
        ]);
        
    }
}
