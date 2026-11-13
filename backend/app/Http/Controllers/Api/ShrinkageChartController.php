<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShrinkageChartRequest;
use App\Models\ShrinkageChart;
use App\Imports\ShrinkageChartImport;
use App\Exports\ShrinkageChartExport;
use Database\Seeders\ShrinkageChartSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ShrinkageChartController extends Controller
{
    /**
     * Display a listing of shrinkage chart rules.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ShrinkageChart::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('group_name', 'like', "%{$search}%")
                  ->orWhere('group_code', 'like', "%{$search}%")
                  ->orWhere('pattern_code', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        if ($stage = $request->input('stage')) {
            if ($stage !== 'All') {
                $query->where('stage', $stage);
            }
        }

        $sortBy = $request->input('sort_by', 'id');
        $sortOrder = $request->input('sort_order', 'asc');
        $allowedSorts = ['id', 'group_name', 'group_code', 'stage', 'length_min', 'length_max', 'width_min', 'width_max', 'is_active'];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        } else {
            $query->orderBy('id', 'asc');
        }

        $perPage = (int)$request->input('per_page', 25);
        $charts = $query->paginate($perPage);

        return response()->json($charts);
    }

    /**
     * Store a new shrinkage chart rule.
     */
    public function store(StoreShrinkageChartRequest $request): JsonResponse
    {
        $chart = ShrinkageChart::create($request->validated());

        return response()->json([
            'message' => 'Shrinkage group rule created successfully.',
            'data' => $chart,
        ], 201);
    }

    /**
     * Display the specified shrinkage chart rule.
     */
    public function show(int $id): JsonResponse
    {
        $chart = ShrinkageChart::withCount('fabricRolls')->findOrFail($id);
        return response()->json($chart);
    }

    /**
     * Update the specified shrinkage chart rule.
     */
    public function update(StoreShrinkageChartRequest $request, int $id): JsonResponse
    {
        $chart = ShrinkageChart::findOrFail($id);
        $chart->update($request->validated());

        return response()->json([
            'message' => 'Shrinkage group rule updated successfully.',
            'data' => $chart,
        ]);
    }

    /**
     * Remove the specified shrinkage chart rule.
     */
    public function destroy(int $id): JsonResponse
    {
        $chart = ShrinkageChart::findOrFail($id);
        $chart->delete();

        return response()->json([
            'message' => 'Shrinkage group rule deleted successfully.',
        ]);
    }

    /**
     * Import shrinkage chart records from CSV or Excel file.
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $import = new ShrinkageChartImport();
        Excel::import($import, $request->file('file'));

        $totalRecords = ShrinkageChart::count();

        return response()->json([
            'message' => "Successfully imported records. Total chart rules in system: {$totalRecords}.",
            'imported_count' => $import->getRowCount(),
            'total_count' => $totalRecords,
        ]);
    }

    /**
     * Export shrinkage chart records to CSV or Excel.
     */
    public function export(Request $request)
    {
        $format = strtolower($request->input('format', 'xlsx'));
        $stage = $request->input('stage');

        $filename = 'shrinkage_chart_master_' . date('Y-m-d_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new ShrinkageChartExport($stage), $filename, $writerType);
    }

    /**
     * One-click seed / reset of 400+ sample chart records.
     */
    public function seedSample(): JsonResponse
    {
        $seeder = new ShrinkageChartSeeder();
        $count = $seeder->run(true);

        return response()->json([
            'message' => "Successfully seeded {$count} shrinkage chart records.",
            'total_count' => ShrinkageChart::count(),
        ]);
    }

    /**
     * Download a ready-to-import CSV sample file with 400+ chart records.
     */
    public function downloadSampleCsv(): BinaryFileResponse
    {
        $path = storage_path('app/sample_shrinkage_chart_400_plus.csv');
        if (!file_exists($path)) {
            // Generate it on the fly if needed
            $seeder = new ShrinkageChartSeeder();
            $seeder->generateCsvFile($path);
        }

        return response()->download($path, 'sample_shrinkage_chart_400_plus.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}

