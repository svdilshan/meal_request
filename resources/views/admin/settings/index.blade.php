@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        
        <!-- Header -->
        <div class="mb-4">
            <h2 class="fw-bold mb-1">System Settings</h2>
            <p class="text-muted">Global cutoff times and request portal availability settings (Super Admin only).</p>
        </div>

        <div class="app-card bg-white">
            <div class="app-card-header border-bottom">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders text-primary me-2"></i> Configure Parameters</h5>
            </div>
            
            <div class="card-body p-4">
                <form action="{{ route('admin.settings.store') }}" method="POST">
                    @csrf
                    
                    <!-- Cutoff times -->
                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-2">Cutoff Times</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="breakfast_cutoff_time" class="form-label small fw-bold">Breakfast Cutoff Time</label>
                            <input type="time" name="breakfast_cutoff_time" id="breakfast_cutoff_time" class="form-control" value="{{ $settings['breakfast_cutoff_time'] }}" required>
                            <small class="text-danger fw-bold d-block mt-1">Relative to PREVIOUS Day</small>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0">
                            <label for="lunch_cutoff_time" class="form-label small fw-bold">Lunch Cutoff Time</label>
                            <input type="time" name="lunch_cutoff_time" id="lunch_cutoff_time" class="form-control" value="{{ $settings['lunch_cutoff_time'] }}" required>
                            <small class="text-secondary fw-bold d-block mt-1">Relative to SAME Day</small>
                        </div>
                        <div class="col-md-4">
                            <label for="dinner_cutoff_time" class="form-label small fw-bold">Dinner Cutoff Time</label>
                            <input type="time" name="dinner_cutoff_time" id="dinner_cutoff_time" class="form-control" value="{{ $settings['dinner_cutoff_time'] }}" required>
                            <small class="text-secondary fw-bold d-block mt-1">Relative to SAME Day</small>
                        </div>
                    </div>

                    <!-- Advance Days -->
                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-2">Advance Scheduling</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="advance_request_days" class="form-label small fw-bold">Advance Request Days (N)</label>
                            <input type="number" name="advance_request_days" id="advance_request_days" class="form-control" min="0" max="14" value="{{ $settings['advance_request_days'] }}" required>
                            <small class="text-muted d-block mt-1">Number of days in advance users can request meals for (N = 2 means Today + next 2 days).</small>
                        </div>
                    </div>

                    <!-- Disable Form controls -->
                    <h5 class="fw-bold mb-3 text-secondary border-bottom pb-2">Portal Status Control</h5>
                    
                    <div class="mb-3">
                        <div class="form-check form-switch p-0 ps-5">
                            <input class="form-check-input" type="checkbox" role="switch" name="form_disabled" id="form_disabled" value="1" {{ $settings['form_disabled'] === '1' ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-danger cursor-pointer" for="form_disabled">Disable Meal Request Form Globally</label>
                        </div>
                        <small class="text-muted d-block ms-5">Toggle to instantly hide the request form and lock out all meal requests.</small>
                    </div>

                    <div class="mb-4 ms-5">
                        <label for="form_disabled_message" class="form-label small fw-bold">Form Disabled Announcement Message</label>
                        <textarea name="form_disabled_message" id="form_disabled_message" class="form-control" rows="3" required>{{ $settings['form_disabled_message'] }}</textarea>
                        <small class="text-muted">This message will be shown on the home dashboard when the portal is disabled.</small>
                    </div>

                    <hr>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 py-2">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
