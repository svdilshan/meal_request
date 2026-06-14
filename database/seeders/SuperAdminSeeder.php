<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'epf_no' => '0000',
                'name' => 'Super Admin',
                'password' => 'Password123',
                'role' => 'super_admin',
                'is_active' => true,
            ]
        );
    }
}
