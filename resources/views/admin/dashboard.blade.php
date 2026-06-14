@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row">
    <!-- Header -->
    <div class="col-12 mb-4">
        <h2 class="fw-bold mb-1">Admin Dashboard</h2>
        <p class="text-muted">Overview of today's meal requests and portal activity.</p>
    </div>

    <!-- Counters -->
    <div class="col-md-4 mb-4">
        <div class="app-card bg-white h-100 border-start border-primary border-4 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size:0.75rem;">Total Meal Requests (Today)</h6>
                    <h2 class="fw-bold mb-0 text-primary">{{ $todayTotalRequests }}</h2>
                </div>
                <div class="bg-primary bg-opacity-10 p-3 rounded-3 text-primary">
                    <i class="bi bi-calendar-check fs-2"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="app-card bg-white h-100 border-start border-success border-4 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size:0.75rem;">Active Accounts</h6>
                    <h2 class="fw-bold mb-0 text-success">{{ $activeUsers }} <small class="text-muted fs-6">/ {{ $totalUsers }}</small></h2>
                </div>
                <div class="bg-success bg-opacity-10 p-3 rounded-3 text-success">
                    <i class="bi bi-people fs-2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="app-card bg-white h-100 border-start border-warning border-4 shadow-sm">
            <div class="card-body p-4 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="text-uppercase text-muted fw-bold mb-1" style="font-size:0.75rem;">Date & Time (Sri Lanka)</h6>
                    <h2 class="fw-bold mb-1 text-warning" style="font-size:1.35rem;" id="live-date">{{ \Carbon\Carbon::now()->format('l, d F Y') }}</h2>
                    <h3 class="fw-normal mb-0 text-secondary" style="font-size:1.15rem;" id="live-time">{{ \Carbon\Carbon::now()->format('h:i:s A') }}</h3>
                </div>
                <div class="bg-warning bg-opacity-10 p-3 rounded-3 text-warning">
                    <i class="bi bi-clock-history fs-2"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Meal breakdowns -->
    <div class="col-lg-8 mb-4">
        <div class="app-card bg-white h-100">
            <div class="app-card-header bg-transparent border-bottom">
                <h5 class="fw-bold mb-0 text-dark">Today's Requests Breakdown</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    @foreach($todayStats as $stat)
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="border rounded-3 p-4 text-center bg-light">
                                <div class="small text-uppercase text-muted fw-bold mb-2">{{ $stat['name'] }}</div>
                                <h1 class="fw-bold mb-3 text-primary">{{ $stat['count'] }}</h1>
                                <a href="{{ route('admin.reports.index', ['start_date' => $todayStr, 'end_date' => $todayStr]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-eye"></i> View List
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Action Card -->
    <div class="col-lg-4 mb-4">
        <div class="app-card bg-white h-100">
            <div class="app-card-header bg-transparent border-bottom">
                <h5 class="fw-bold mb-0 text-dark">Quick Administrative Actions</h5>
            </div>
            <div class="card-body p-4 d-flex flex-column gap-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-primary w-100 py-3 text-start d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-people-fill me-2"></i> Manage Users</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary w-100 py-3 text-start d-flex align-items-center justify-content-between">
                    <span><i class="bi bi-file-earmark-bar-graph-fill me-2"></i> Export Meal Reports</span>
                    <i class="bi bi-chevron-right"></i>
                </a>
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-danger w-100 py-3 text-start d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-gear-fill me-2"></i> System Settings</span>
                        <i class="bi bi-chevron-right"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function updateSriLankaTime() {
        const optionsDate = {
            timeZone: 'Asia/Colombo',
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const optionsTime = {
            timeZone: 'Asia/Colombo',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        };
        
        const now = new Date();
        const dateString = now.toLocaleDateString('en-US', optionsDate);
        const timeString = now.toLocaleTimeString('en-US', optionsTime);
        
        const dateElement = document.getElementById('live-date');
        const timeElement = document.getElementById('live-time');
        
        if (dateElement) dateElement.textContent = dateString;
        if (timeElement) timeElement.textContent = timeString;
    }
    
    // Run immediately and then set interval to update every second
    updateSriLankaTime();
    setInterval(updateSriLankaTime, 1000);
</script>
@endsection
