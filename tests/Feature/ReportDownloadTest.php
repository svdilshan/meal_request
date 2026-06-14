<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\MealType;
use App\Models\MealRequest;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->admin = User::create([
            'epf_no' => '9999',
            'name' => 'Admin User',
            'username' => 'admin',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create active normal user
        $user = User::create([
            'epf_no' => '1111',
            'name' => 'Test User',
            'username' => 'testuser',
            'password' => 'password',
            'role' => 'user',
            'is_active' => true,
        ]);

        // Create meal types
        $breakfast = MealType::create(['name' => 'Breakfast', 'slug' => 'breakfast', 'sort_order' => 1, 'is_active' => true]);
        $lunch = MealType::create(['name' => 'Lunch', 'slug' => 'lunch', 'sort_order' => 2, 'is_active' => true]);
        $dinner = MealType::create(['name' => 'Dinner', 'slug' => 'dinner', 'sort_order' => 3, 'is_active' => true]);

        // Create meal request
        MealRequest::create([
            'user_id' => $user->id,
            'meal_type_id' => $lunch->id,
            'request_date' => Carbon::today()->format('Y-m-d'),
            'submitted_at' => Carbon::now(),
        ]);
    }

    public function test_admin_can_download_report()
    {
        $startDate = Carbon::today()->startOfWeek()->format('Y-m-d');
        $endDate = Carbon::today()->endOfWeek()->format('Y-m-d');

        $response = $this->actingAs($this->admin)->get(route('admin.reports.download', [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_admin_can_download_user_import_template()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.users.template'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
