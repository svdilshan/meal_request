<?php

namespace Database\Seeders;

use App\Models\MealType;
use Illuminate\Database\Seeder;

class MealTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MealType::updateOrCreate(
            ['slug' => 'breakfast'],
            ['name' => 'Breakfast', 'sort_order' => 1, 'is_active' => true]
        );

        MealType::updateOrCreate(
            ['slug' => 'lunch'],
            ['name' => 'Lunch', 'sort_order' => 2, 'is_active' => true]
        );

        MealType::updateOrCreate(
            ['slug' => 'dinner'],
            ['name' => 'Dinner', 'sort_order' => 3, 'is_active' => true]
        );
    }
}
