<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        $settings = [
            'breakfast_cutoff_time' => Setting::get('breakfast_cutoff_time', '23:59'),
            'lunch_cutoff_time' => Setting::get('lunch_cutoff_time', '10:00'),
            'dinner_cutoff_time' => Setting::get('dinner_cutoff_time', '16:00'),
            'advance_request_days' => Setting::get('advance_request_days', '2'),
            'form_disabled' => Setting::get('form_disabled', '0'),
            'form_disabled_message' => Setting::get('form_disabled_message', 'The meal request form is currently disabled. Please contact the admin.'),
        ];

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Store updated settings.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'breakfast_cutoff_time' => ['required', 'date_format:H:i'],
            'lunch_cutoff_time' => ['required', 'date_format:H:i'],
            'dinner_cutoff_time' => ['required', 'date_format:H:i'],
            'advance_request_days' => ['required', 'integer', 'min:0'],
            'form_disabled' => ['nullable', 'in:0,1'],
            'form_disabled_message' => ['required', 'string', 'max:500'],
        ]);

        $userId = auth()->id();

        Setting::set('breakfast_cutoff_time', $validated['breakfast_cutoff_time'], $userId);
        Setting::set('lunch_cutoff_time', $validated['lunch_cutoff_time'], $userId);
        Setting::set('dinner_cutoff_time', $validated['dinner_cutoff_time'], $userId);
        Setting::set('advance_request_days', (string)$validated['advance_request_days'], $userId);
        Setting::set('form_disabled', $request->has('form_disabled') ? '1' : '0', $userId);
        Setting::set('form_disabled_message', $validated['form_disabled_message'], $userId);

        return back()->with('success', 'Settings updated successfully.');
    }
}
