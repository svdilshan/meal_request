<?php

namespace App\Http\Controllers\Admin;

use App\Exports\MealReportExport;
use App\Http\Controllers\Controller;
use App\Models\MealRequest;
use App\Models\MealType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Display the reports page.
     */
    public function index(Request $request)
    {
        // Clean up old report files (older than 1 hour)
        $tempDir = storage_path('app/private/temp');
        if (\Illuminate\Support\Facades\File::exists($tempDir)) {
            $files = \Illuminate\Support\Facades\File::files($tempDir);
            foreach ($files as $file) {
                if (time() - $file->getMTime() > 3600) {
                    \Illuminate\Support\Facades\File::delete($file->getRealPath());
                }
            }
        }

        $startDateStr = $request->input('start_date', Carbon::today()->startOfWeek()->format('Y-m-d'));
        $endDateStr = $request->input('end_date', Carbon::today()->endOfWeek()->format('Y-m-d'));

        $startDate = Carbon::parse($startDateStr)->startOfDay();
        $endDate = Carbon::parse($endDateStr)->endOfDay();

        $mealTypes = MealType::orderBy('sort_order')->get();
        $summary = [];
        $total = 0;

        foreach ($mealTypes as $type) {
            $count = MealRequest::where('meal_type_id', $type->id)
                ->whereBetween('request_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->count();
            $summary[] = [
                'name' => $type->name,
                'count' => $count,
            ];
            $total += $count;
        }

        return view('admin.reports.index', [
            'startDate' => $startDateStr,
            'endDate' => $endDateStr,
            'summary' => $summary,
            'total' => $total,
        ]);
    }

    /**
     * Download the Excel report.
     */
    public function download(Request $request)
    {
        $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
        $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

        $filename = 'MealReport_' . $startDate . '_to_' . $endDate . '.xlsx';
        $tempPath = 'temp/' . $filename;

        // Store the file locally first to avoid stream/locking errors on Windows
        Excel::store(new MealReportExport($startDate, $endDate), $tempPath, 'local');

        $fullPath = storage_path('app/private/' . $tempPath);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }

        if (app()->environment('testing')) {
            return response()->download($fullPath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]);
        }

        // Set headers manually to ensure correct filename and content type
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));

        // Clear output buffer to ensure no trailing/leading whitespace or errors corrupt the file
        if (ob_get_level()) {
            ob_end_clean();
        }

        readfile($fullPath);
        exit;
    }

    /**
     * Display the Admin Dashboard.
     */
    public function dashboard()
    {
        $todayStr = Carbon::today()->format('Y-m-d');

        // Today's summary counts
        $mealTypes = MealType::where('is_active', true)->orderBy('sort_order')->get();
        $todayStats = [];
        foreach ($mealTypes as $type) {
            $todayStats[] = [
                'name' => $type->name,
                'count' => MealRequest::where('meal_type_id', $type->id)
                    ->where('request_date', $todayStr)
                    ->count()
            ];
        }

        $totalUsers = User::count();
        $activeUsers = User::where('is_active', 1)->count();
        $todayTotalRequests = MealRequest::where('request_date', $todayStr)->count();

        return view('admin.dashboard', compact('todayStats', 'totalUsers', 'activeUsers', 'todayTotalRequests', 'todayStr'));
    }
}
