@extends('layouts.app')

@section('title', 'Excel Export Reports')

@section('content')
<div class="row">
    <!-- Header -->
    <div class="col-12 mb-4">
        <h2 class="fw-bold mb-1">Meal Request Reports</h2>
        <p class="text-muted">Generate and download multi-sheet Excel reports of employee meal requests.</p>
    </div>

    <!-- Date selector card -->
    <div class="col-lg-4 mb-4">
        <div class="app-card bg-white h-100">
            <div class="app-card-header">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-range text-primary me-2"></i> Select Date Range</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.reports.index') }}" method="GET" class="mb-3">
                    <div class="mb-3">
                        <label for="start_date" class="form-label small fw-bold">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}" required>
                    </div>
                    <div class="mb-4">
                        <label for="end_date" class="form-label small fw-bold">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}" required>
                    </div>
                    <button type="submit" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-arrow-clockwise"></i> Update Preview
                    </button>
                </form>

                <a href="{{ route('admin.reports.download', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-accent w-100 py-2.5">
                    <i class="bi bi-file-earmark-arrow-down-fill me-1"></i> Download Excel Report
                </a>
            </div>
        </div>
    </div>

    <!-- Preview card -->
    <div class="col-lg-8 mb-4">
        <div class="app-card bg-white h-100">
            <div class="app-card-header d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-eye text-primary me-2"></i> Report Summary Preview</h5>
                <span class="badge bg-secondary">
                    {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    <div class="col-12 mb-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle border-top">
                                <thead class="table-light">
                                    <tr>
                                        <th>Meal Type</th>
                                        <th class="text-end">Total Requests Count</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($summary as $item)
                                        <tr>
                                            <td class="fw-semibold text-dark">{{ $item['name'] }}</td>
                                            <td class="text-end fw-bold text-primary">{{ $item['count'] }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-light fw-bold border-top border-2">
                                        <td class="text-dark">Total Meal Counts</td>
                                        <td class="text-end text-danger fs-5">{{ $total }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-12 text-center text-muted small bg-light p-3 rounded border">
                        <i class="bi bi-file-earmark-excel-fill text-success fs-4 d-block mb-1"></i>
                        The downloaded Excel file will contain four sheets:
                        <strong class="text-dark">Breakfast</strong>, <strong class="text-dark">Lunch</strong>, and <strong class="text-dark">Dinner</strong> matching requests with EPF numbers, plus a summary matching this preview.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
