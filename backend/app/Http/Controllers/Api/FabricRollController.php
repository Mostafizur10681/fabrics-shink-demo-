<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFabricRollRequest;
use App\Http\Requests\UpdateFabricRollRequest;
use App\Models\FabricRoll;
use App\Services\FabricShrinkageService;
use App\Exports\FabricRollExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FabricRollController extends Controller
{
    protected FabricShrinkageService $shrinkageService;

    public function __construct(FabricShrinkageService $shrinkageService)
    {
        $this->shrinkageService = $shrinkageService;
    }

    /**
     * Display a listing of fabric rolls with filtering, search, and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $query = FabricRoll::query()->with('matchedChart');

        // Text search across roll_number, lot_number, fabric_type, buyer, order_number, matched_group
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('roll_number', 'like', "%{$search}%")
                  ->orWhere('lot_number', 'like', "%{$search}%")
                  ->orWhere('fabric_type', 'like', "%{$search}%")
                  ->orWhere('buyer', 'like', "%{$search}%")
                  ->orWhere('order_number', 'like', "%{$search}%")
                  ->orWhere('matched_group', 'like', "%{$search}%");
            });
        }

        // Filter by stage
        if ($stage = $request->input('stage')) {
            if ($stage !== 'All') {
                $query->where('stage', $stage);
            }
        }

        // Filter by classification status
        if ($status = $request->input('status')) {
            if ($status !== 'All') {
                $query->where('classification_status', $status);
            }
        }

        // Filter by specific group
        if ($group = $request->input('group')) {
            if ($group !== 'All') {
                $query->where('matched_group', $group);
            }
        }

        // Filter by buyer
        if ($buyer = $request->input('buyer')) {
            if ($buyer !== 'All') {
                $query->where('buyer', $buyer);
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $allowedSorts = [
            'id', 'roll_number', 'lot_number', 'fabric_type', 'stage',
            'test_date', 'length_shrinkage_avg', 'width_shrinkage_avg',
            'matched_group', 'classification_status', 'created_at'
        ];

        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = (int)$request->input('per_page', 15);
        $rolls = $query->paginate($perPage);

        return response()->json($rolls);
    }

    /**
     * Store a newly created fabric roll with automatic calculation and classification.
     */
    public function store(StoreFabricRollRequest $request): JsonResponse
    {
        $data = $request->validated();

        $roll = new FabricRoll($data);

        // Calculate 6-reading averages and classify against shrinkage chart rules
        $this->shrinkageService->classifyRoll($roll, true);

        return response()->json([
            'message' => 'Fabric roll created and classified successfully.',
            'data' => $roll->load('matchedChart'),
        ], 201);
    }

    /**
     * Display the specified fabric roll.
     */
    public function show(int $id): JsonResponse
    {
        $roll = FabricRoll::with('matchedChart')->findOrFail($id);
        return response()->json($roll);
    }

    /**
     * Update the specified fabric roll and recalculate averages and classification.
     */
    public function update(UpdateFabricRollRequest $request, int $id): JsonResponse
    {
        $roll = FabricRoll::findOrFail($id);
        $roll->fill($request->validated());

        $this->shrinkageService->classifyRoll($roll, true);

        return response()->json([
            'message' => 'Fabric roll updated and re-classified successfully.',
            'data' => $roll->load('matchedChart'),
        ]);
    }

    /**
     * Remove the specified fabric roll from storage.
     */
    public function destroy(int $id): JsonResponse
    {
        $roll = FabricRoll::findOrFail($id);
        $roll->delete();

        return response()->json([
            'message' => 'Fabric roll deleted successfully.',
        ]);
    }

    /**
     * Interactive live preview of shrinkage averages and group classification.
     */
    public function previewClassification(Request $request): JsonResponse
    {
        $request->validate([
            'length_reading_1' => 'required|numeric',
            'length_reading_2' => 'required|numeric',
            'length_reading_3' => 'required|numeric',
            'width_reading_1' => 'required|numeric',
            'width_reading_2' => 'required|numeric',
            'width_reading_3' => 'required|numeric',
            'stage' => 'required|string',
            'fabric_type' => 'nullable|string',
        ]);

        $averages = $this->shrinkageService->calculateAverages(
            (float)$request->length_reading_1,
            (float)$request->length_reading_2,
            (float)$request->length_reading_3,
            (float)$request->width_reading_1,
            (float)$request->width_reading_2,
            (float)$request->width_reading_3
        );

        $classification = $this->shrinkageService->classify(
            $averages['length_avg'],
            $averages['width_avg'],
            $request->stage,
            $request->fabric_type
        );

        return response()->json([
            'averages' => $averages,
            'classification' => $classification,
        ]);
    }

    /**
     * Batch reclassify all rolls (optionally filtered by stage).
     */
    public function classifyAll(Request $request): JsonResponse
    {
        $stage = $request->input('stage');
        $count = $this->shrinkageService->classifyAll($stage);

        return response()->json([
            'message' => "Successfully re-classified {$count} fabric rolls.",
            'count' => $count,
        ]);
    }

    /**
     * Export rolls to Excel (.xlsx) or CSV.
     */
    public function export(Request $request)
    {
        $format = strtolower($request->input('format', 'xlsx'));
        $stage = $request->input('stage');
        $status = $request->input('status');

        $filename = 'fabric_rolls_' . date('Y-m-d_His') . '.' . ($format === 'csv' ? 'csv' : 'xlsx');
        $writerType = $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX;

        return Excel::download(new FabricRollExport($stage, $status), $filename, $writerType);
    }
}

