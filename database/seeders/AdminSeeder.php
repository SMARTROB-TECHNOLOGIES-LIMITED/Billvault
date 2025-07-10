<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::truncate();        
        $admin = Admin::create([
            'first_name' => 'Admin',
            'surname' => 'Admin',
            'password' => Hash::make('password'),
            'email' => 'admin@paypointapp.africa',
            'phone_number' => '0909778899',
            'profile' => '',
            'view' => 1,
        ]);
    }
}
