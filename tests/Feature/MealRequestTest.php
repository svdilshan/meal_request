<?php

namespace Tests\Feature;

use App\Models\MealType;
use App\Models\MealRequest;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealRequestTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $mealType;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
        Setting::set('breakfast_cutoff_time', '23:59');
        Setting::set('lunch_cutoff_time', '10:00');
        Setting::set('advance_request_days', '2');
        Setting::set('form_disabled', '0');

        // Create Meal Type
        $this->mealType = MealType::create([
            'name' => 'Lunch',
            'slug' => 'lunch',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // Create active normal user
        $this->user = User::create([
            'epf_no' => '1111',
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);
    }

    public function test_user_can_submit_meal_request_within_cutoff()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(9, 30)); // 9:30 AM (before 10:00 AM cutoff)

        $response = $this->actingAs($this->user)->post('/request', [
            'date' => $today->format('Y-m-d'),
            'meal_type_id' => $this->mealType->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('meal_requests', [
            'user_id' => $this->user->id,
            'meal_type_id' => $this->mealType->id,
            'request_date' => $today->format('Y-m-d'),
        ]);

        Carbon::setTestNow();
    }

    public function test_user_cannot_request_past_cutoff()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(10, 30)); // 10:30 AM (after 10:00 AM cutoff)

        $response = $this->actingAs($this->user)->post('/request', [
            'date' => $today->format('Y-m-d'),
            'meal_type_id' => $this->mealType->id,
        ]);

        $response->assertSessionHasErrors(['meal_type_id']);
        $this->assertDatabaseMissing('meal_requests', [
            'user_id' => $this->user->id,
        ]);

        Carbon::setTestNow();
    }

    public function test_user_cannot_submit_duplicate_request()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(9, 30));

        // Create first request
        MealRequest::create([
            'user_id' => $this->user->id,
            'meal_type_id' => $this->mealType->id,
            'request_date' => $today->format('Y-m-d'),
            'submitted_at' => Carbon::now(),
        ]);

        // Attempt duplicate post request
        $response = $this->actingAs($this->user)->post('/request', [
            'date' => $today->format('Y-m-d'),
            'meal_type_id' => $this->mealType->id,
        ]);

        $response->assertSessionHasErrors(['meal_type_id']);
        $this->assertEquals(1, MealRequest::count());

        Carbon::setTestNow();
    }

    public function test_user_cannot_request_outside_advance_range()
    {
        $today = Carbon::today();
        Carbon::setTestNow($today->copy()->setTime(9, 30));

        // N = 2, so max date is today + 2. Let's try requesting for today + 3
        $futureDate = $today->copy()->addDays(3);

        $response = $this->actingAs($this->user)->post('/request', [
            'date' => $futureDate->format('Y-m-d'),
            'meal_type_id' => $this->mealType->id,
        ]);

        $response->assertSessionHasErrors(['date']);
        $this->assertDatabaseMissing('meal_requests', [
            'request_date' => $futureDate->format('Y-m-d'),
        ]);

        Carbon::setTestNow();
    }

    public function test_deactivated_user_cannot_request_meals()
    {
        $this->user->is_active = false;
        $this->user->save();

        $today = Carbon::today();

        // Should redirect to login and log out the deactivated user via CheckRole middleware
        $response = $this->actingAs($this->user)->get('/request');
        $response->assertRedirect('/login');
        $this->assertFalse(auth()->check());
    }
}
