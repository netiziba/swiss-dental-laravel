<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@swiss-dental.ch'],
            [
                'first_name' => 'Admin',
                'last_name'  => 'Swiss Dental',
                'password'   => Hash::make('Admin@1234!'),
            ]
        );

        UserRole::firstOrCreate([
            'user_id' => $user->id,
            'role'    => 'admin',
        ]);

        $this->command->info('Admin created: admin@swiss-dental.ch / Admin@1234!');
    }
}
