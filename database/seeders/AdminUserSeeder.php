<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'bkaynar998@gmail.com'],
            [
                'name' => 'admin_burak',
                'password' => Hash::make('admin_burak'),
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'kurumsal@asyabilisim.com'],
            [
                'name' => 'admin_burak1',
                'password' => Hash::make('admin_burak1'),
                'email_verified_at' => now(),
            ]
        );
    }
}