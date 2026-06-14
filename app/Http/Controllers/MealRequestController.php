<?php

namespace App\Http\Controllers;

use App\Helpers\MealCutoffHelper;
use App\Models\MealRequest;
use App\Models\MealType;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MealRequestController extends Controller
{
    /**
     * Display the meal request form.
     */
    public function index()
    {
        $formDisabled = Setting::get('form_disabled', '0') === '1';
        $disabledMessage = Setting::get('form_disabled_message', 'The meal request form is currently disabled. Please contact the admin.');

        if ($formDisabled) {
            return view('request.index', [
                'formDisabled' => true,
                'disabledMessage' => $disabledMessage,
            ]);
        }

        $user = Auth::user();
        $advanceDays = (int)Setting::get('advance_request_days', 2);
        $mealTypes = MealType::where('is_active', true)->orderBy('sort_order')->get();

        $dates = [];
        $today = Carbon::today();

        for ($i = 0; $i <= $advanceDays; $i++) {
            $date = $today->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            
            // Check existing user requests for this date
            $userRequests = MealRequest::where('user_id', $user->id)
                ->where('request_date', $dateStr)
                ->pluck('meal_type_id')
                ->toArray();

            $mealsData = [];
            foreach ($mealTypes as $mealType) {
                $isAvailable = MealCutoffHelper::isMealAvailable($mealType->slug, $date);
                $isRequested = in_array($mealType->id, $userRequests);

                $mealsData[] = [
                    'id' => $mealType->id,
                    'name' => $mealType->name,
                    'slug' => $mealType->slug,
                    'is_available' => $isAvailable,
                    'is_requested' => $isRequested,
                ];
            }

            $dates[] = [
                'date_string' => $dateStr,
                'label' => $date->format('l, d M'),
                'is_today' => $i === 0,
                'meals' => $mealsData,
            ];
        }

        return view('request.index', [
            'formDisabled' => false,
            'user' => $user,
            'dates' => $dates,
        ]);
    }

    /**
     * Submit a meal request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'meal_type_id' => ['required', 'exists:meal_types,id'],
        ]);

        if (Setting::get('form_disabled', '0') === '1') {
            return back()->withErrors(['form' => 'The meal request form is currently disabled.']);
        }

        $user = Auth::user();
        $dateStr = $request->date;
        $requestDate = Carbon::parse($dateStr);
        $today = Carbon::today();

        // 1. Verify date is within bounds: Today to Today + N
        $advanceDays = (int)Setting::get('advance_request_days', 2);
        $maxDate = $today->copy()->addDays($advanceDays);

        if ($requestDate->lt($today) || $requestDate->gt($maxDate)) {
            return back()->withErrors(['date' => 'Selected date is outside the permitted range.']);
        }

        // 2. Find meal type and verify cutoff logic
        $mealType = MealType::findOrFail($request->meal_type_id);
        if (!$mealType->is_active) {
            return back()->withErrors(['meal_type_id' => 'The selected meal type is currently disabled.']);
        }

        if (!MealCutoffHelper::isMealAvailable($mealType->slug, $requestDate)) {
            return back()->withErrors(['meal_type_id' => 'The cutoff time for this meal has passed.']);
        }

        // 3. Verify duplicate request (unique user + meal_type + request_date)
        $existing = MealRequest::where('user_id', $user->id)
            ->where('meal_type_id', $mealType->id)
            ->where('request_date', $dateStr)
            ->first();

        if ($existing) {
            return back()->withErrors(['meal_type_id' => 'You have already requested ' . $mealType->name . ' for this date.']);
        }

        // 4. Save the request
        MealRequest::create([
            'user_id' => $user->id,
            'meal_type_id' => $mealType->id,
            'request_date' => $dateStr,
            'submitted_at' => Carbon::now(),
        ]);

        return back()->with('success', 'Your request for ' . $mealType->name . ' on ' . $requestDate->format('l, d M') . ' has been submitted successfully.');
    }
}
