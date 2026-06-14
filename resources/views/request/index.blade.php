@extends('layouts.app')

@section('title', 'Meal Request Form')

@section('styles')
<style>
    .card-radio input[type="radio"]:checked + label {
        border-color: var(--app-blue) !important;
        background-color: var(--app-blue-light) !important;
        box-shadow: 0 0 0 2px var(--app-blue);
    }

    .card-radio input[type="radio"]:disabled + label {
        opacity: 0.65;
        cursor: not-allowed;
        background-color: #f8f9fa;
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .nav-tabs .nav-link {
        font-family: 'Outfit', sans-serif;
        font-weight: 500;
        border: none;
        color: var(--text-muted);
        padding: 1rem 1.5rem;
        transition: all 0.2s ease;
    }

    .nav-tabs .nav-link:hover {
        color: var(--app-blue);
        background-color: rgba(0, 48, 135, 0.03);
    }

    .nav-tabs .nav-link.active {
        color: var(--app-blue);
        border-bottom: 3px solid var(--app-blue);
        font-weight: 700;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        
        @if($formDisabled)
            <!-- Form Globally Disabled View -->
            <div class="app-card text-center p-5 border-top border-danger border-4">
                <i class="bi bi-exclamation-octagon text-danger display-1 mb-4"></i>
                <h2 class="fw-bold mb-3 text-dark">Form Temporarily Disabled</h2>
                <div class="lead text-muted mb-4">{{ $disabledMessage }}</div>
                <div class="d-flex justify-content-center">
                    <span class="badge bg-secondary p-2"><i class="bi bi-info-circle me-1"></i> Contact Admin for Details</span>
                </div>
            </div>
        @else
            <!-- Normal Meal Request Form -->
            <div class="mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h2 class="fw-bold mb-1">Meal Request Form</h2>
                    <p class="text-muted mb-0">Select a date and choose your meal option below.</p>
                </div>
                <div class="bg-white px-3 py-2 rounded shadow-sm border small d-flex align-items-center">
                    <i class="bi bi-clock-fill text-primary me-2"></i>
                    <div>
                        <div class="fw-bold text-dark" style="font-size:0.75rem;">CURRENT TIME</div>
                        <div class="fw-bold text-primary" style="font-size:0.9rem;">{{ now()->format('h:i A') }}</div>
                    </div>
                </div>
            </div>

            <!-- Read-only Employee Info Card -->
            <div class="app-card mb-4 bg-white">
                <div class="card-body p-4">
                    <div class="row align-items-center">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="small text-uppercase text-muted fw-bold">Employee Name</div>
                            <h4 class="text-dark mb-0 fw-semibold">{{ $user->name }}</h4>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-uppercase text-muted fw-bold">EPF Number</div>
                            <h4 class="text-dark mb-0 fw-semibold">{{ $user->epf_no }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date selection Tabs and Forms -->
            <div class="app-card overflow-hidden">
                <div class="bg-white border-bottom">
                    <ul class="nav nav-tabs border-0" id="dateTabs" role="tablist">
                        @foreach($dates as $index => $date)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link w-100 {{ $index === 0 ? 'active' : '' }}" 
                                        id="tab-{{ $date['date_string'] }}" 
                                        data-bs-toggle="tab" 
                                        data-bs-target="#pane-{{ $date['date_string'] }}" 
                                        type="button" 
                                        role="tab" 
                                        aria-controls="pane-{{ $date['date_string'] }}" 
                                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}">
                                    <span class="d-block small text-uppercase fw-bold {{ $index === 0 ? 'text-primary' : '' }}">
                                        {{ $index === 0 ? 'Today' : 'Advance' }}
                                    </span>
                                    <span class="fs-6">{{ $date['label'] }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="tab-content" id="dateTabsContent">
                        @foreach($dates as $index => $date)
                            <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}" 
                                 id="pane-{{ $date['date_string'] }}" 
                                 role="tabpanel" 
                                 aria-labelledby="tab-{{ $date['date_string'] }}">
                                
                                <form action="{{ route('request.store') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date['date_string'] }}">
                                    
                                    <h5 class="fw-bold mb-3 text-secondary">Choose a Meal for {{ $date['label'] }}:</h5>

                                    <div class="row">
                                        @foreach($date['meals'] as $meal)
                                            <div class="col-12 mb-3">
                                                <div class="form-check card-radio p-0 m-0">
                                                    <input class="form-check-input d-none" 
                                                           type="radio" 
                                                           name="meal_type_id" 
                                                           id="meal_{{ $date['date_string'] }}_{{ $meal['id'] }}" 
                                                           value="{{ $meal['id'] }}" 
                                                           {{ !$meal['is_available'] || $meal['is_requested'] ? 'disabled' : '' }} 
                                                           required>
                                                    <label class="form-check-label d-block p-3 border rounded-3 cursor-pointer position-relative shadow-sm" 
                                                           for="meal_{{ $date['date_string'] }}_{{ $meal['id'] }}">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="fw-bold fs-5 d-block text-dark">{{ $meal['name'] }}</span>
                                                                <small class="text-muted d-block mt-1">
                                                                    <i class="bi bi-clock me-1"></i> Cutoff Time: 
                                                                    <span class="fw-medium text-dark">{{ App\Models\Setting::get($meal['slug'] . '_cutoff_time') }}</span>
                                                                    @if($meal['slug'] === 'breakfast')
                                                                        <span class="text-danger fw-bold ms-1">(Previous Day)</span>
                                                                    @else
                                                                        <span class="text-secondary fw-bold ms-1">(Same Day)</span>
                                                                    @endif
                                                                </small>
                                                            </div>
                                                            <div>
                                                                @if($meal['is_requested'])
                                                                    <span class="badge bg-success p-2 rounded-pill shadow-sm"><i class="bi bi-check-circle-fill me-1"></i> Already Requested</span>
                                                                @elseif(!$meal['is_available'])
                                                                    <span class="badge bg-secondary p-2 rounded-pill"><i class="bi bi-lock-fill me-1"></i> Cutoff Passed</span>
                                                                @else
                                                                    <span class="badge bg-outline-primary border-primary border text-primary p-2 rounded-pill"><i class="bi bi-plus-circle me-1"></i> Select Option</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Only show submit button if at least one meal is requestable -->
                                    @php
                                        $hasRequestableMeal = collect($date['meals'])->contains(function($m) {
                                            return $m['is_available'] && !$m['is_requested'];
                                        });
                                    @endphp

                                    <div class="mt-4 text-end">
                                        @if($hasRequestableMeal)
                                            <button type="submit" class="btn btn-primary px-4 py-2">
                                                <i class="bi bi-send-check me-1"></i> Submit Request
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-secondary px-4 py-2" disabled>
                                                <i class="bi bi-slash-circle me-1"></i> No Option Available
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
