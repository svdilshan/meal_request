<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            'breakfast_cutoff_time' => '23:59',
            'lunch_cutoff_time' => '10:00',
            'dinner_cutoff_time' => '16:00',
            'advance_request_days' => '2',
            'form_disabled' => '0',
            'form_disabled_message' => 'The meal request form is currently disabled. Please contact the admin.',
        ];

        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }
}
