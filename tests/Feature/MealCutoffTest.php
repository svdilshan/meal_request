<?php

namespace Tests\Feature;

use App\Helpers\MealCutoffHelper;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealCutoffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default settings
        Setting::set('breakfast_cutoff_time', '23:59');
        Setting::set('lunch_cutoff_time', '10:00');
        Setting::set('dinner_cutoff_time', '16:00');
    }

    public function test_breakfast_cutoff_previous_day()
    {
        $requestDate = Carbon::parse('2026-06-15');

        // Available: June 14th at 11:58 PM
        Carbon::setTestNow(Carbon::parse('2026-06-14 23:58:00'));
        $this->assertTrue(MealCutoffHelper::isMealAvailable('breakfast', $requestDate));

        // Expired: June 15th at 12:01 AM (since cutoff is June 14th 11:59 PM)
        Carbon::setTestNow(Carbon::parse('2026-06-15 00:01:00'));
        $this->assertFalse(MealCutoffHelper::isMealAvailable('breakfast', $requestDate));

        Carbon::setTestNow(); // Reset time mock
    }

    public function test_lunch_cutoff_same_day()
    {
        $requestDate = Carbon::parse('2026-06-15');

        // Available: June 15th at 9:59 AM
        Carbon::setTestNow(Carbon::parse('2026-06-15 09:59:00'));
        $this->assertTrue(MealCutoffHelper::isMealAvailable('lunch', $requestDate));

        // Expired: June 15th at 10:01 AM
        Carbon::setTestNow(Carbon::parse('2026-06-15 10:01:00'));
        $this->assertFalse(MealCutoffHelper::isMealAvailable('lunch', $requestDate));

        Carbon::setTestNow(); // Reset time mock
    }

    public function test_dinner_cutoff_same_day()
    {
        $requestDate = Carbon::parse('2026-06-15');

        // Available: June 15th at 3:59 PM
        Carbon::setTestNow(Carbon::parse('2026-06-15 15:59:00'));
        $this->assertTrue(MealCutoffHelper::isMealAvailable('dinner', $requestDate));

        // Expired: June 15th at 4:01 PM
        Carbon::setTestNow(Carbon::parse('2026-06-15 16:01:00'));
        $this->assertFalse(MealCutoffHelper::isMealAvailable('dinner', $requestDate));

        Carbon::setTestNow(); // Reset time mock
    }
}
