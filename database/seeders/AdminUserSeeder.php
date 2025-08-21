<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'admin_burak',
            'email' => 'bkaynar998@gmail.com',
            'password' => Hash::make('admin_burak'),
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'admin_burak1',
            'email' => 'kurumsal@asyabilisim.com',
            'password' => Hash::make('admin_burak1'),
            'email_verified_at' => now(),
        ]);
    }
}