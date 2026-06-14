<?php

namespace App\Helpers;

use App\Models\Setting;
use Carbon\Carbon;

class MealCutoffHelper
{
    /**
     * Check if a meal is still available to request on a given date.
     */
    public static function isMealAvailable(string $mealSlug, Carbon $requestDate): bool
    {
        $now = Carbon::now();
        
        $cutoffStr = Setting::get($mealSlug . '_cutoff_time');
        
        if (!$cutoffStr) {
            $cutoffStr = match ($mealSlug) {
                'breakfast' => '23:59',
                'lunch' => '10:00',
                'dinner' => '16:00',
                default => '23:59'
            };
        }

        if ($mealSlug === 'breakfast') {
            // Breakfast cutoff is relative to the previous day
            $cutoff = $requestDate->copy()->subDay()->setTimeFromTimeString($cutoffStr);
        } else {
            // Lunch and Dinner cutoff is relative to the same day
            $cutoff = $requestDate->copy()->setTimeFromTimeString($cutoffStr);
        }

        return $now->lessThan($cutoff);
    }
}
