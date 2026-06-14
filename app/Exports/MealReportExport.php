<?php

namespace App\Exports;

use App\Models\MealRequest;
use App\Models\MealType;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MealReportExport implements WithMultipleSheets
{
    protected $startDate;
    protected $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function sheets(): array
    {
        return [
            new MealRequestsSheet('Breakfast', 'breakfast', $this->startDate, $this->endDate),
            new MealRequestsSheet('Lunch', 'lunch', $this->startDate, $this->endDate),
            new MealRequestsSheet('Dinner', 'dinner', $this->startDate, $this->endDate),
            new SummarySheet($this->startDate, $this->endDate),
        ];
    }
}

class MealRequestsSheet implements FromQuery, WithTitle, WithHeadings, WithMapping
{
    protected $title;
    protected $slug;
    protected $startDate;
    protected $endDate;

    public function __construct(string $title, string $slug, string $startDate, string $endDate)
    {
        $this->title = $title;
        $this->slug = $slug;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        return MealRequest::query()
            ->join('users', 'meal_requests.user_id', '=', 'users.id')
            ->join('meal_types', 'meal_requests.meal_type_id', '=', 'meal_types.id')
            ->where('meal_types.slug', $this->slug)
            ->whereBetween('meal_requests.request_date', [$this->startDate, $this->endDate])
            ->select('users.name as user_name', 'users.epf_no as user_epf')
            ->orderBy('users.name');
    }

    public function title(): string
    {
        return $this->title;
    }

    public function headings(): array
    {
        return [
            'Name',
            'EPF No',
        ];
    }

    public function map($row): array
    {
        return [
            $row->user_name,
            $row->user_epf,
        ];
    }
}

class SummarySheet implements FromArray, WithTitle, WithHeadings
{
    protected $startDate;
    protected $endDate;

    public function __construct(string $startDate, string $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return [
            'Meal Type',
            'Count',
        ];
    }

    public function array(): array
    {
        $mealTypes = MealType::orderBy('sort_order')->get();
        $data = [];
        $total = 0;

        foreach ($mealTypes as $type) {
            $count = MealRequest::where('meal_type_id', $type->id)
                ->whereBetween('request_date', [$this->startDate, $this->endDate])
                ->count();
            
            $data[] = [
                'Meal Type' => $type->name,
                'Count' => $count,
            ];
            $total += $count;
        }

        $data[] = [
            'Meal Type' => 'Total',
            'Count' => $total,
        ];

        return $data;
    }
}
