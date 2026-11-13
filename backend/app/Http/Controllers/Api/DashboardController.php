<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FabricRoll;
use App\Models\ShrinkageChart;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $totalRolls = FabricRoll::count();
        $totalChartRules = ShrinkageChart::count();

        $matchedCount = FabricRoll::where('classification_status', 'MATCHED')->count();
        $noMatchCount = FabricRoll::where('classification_status', 'NO_MATCH')->count();
        $multipleMatchesCount = FabricRoll::where('classification_status', 'MULTIPLE_MATCHES')->count();
        $pendingCount = FabricRoll::where('classification_status', 'PENDING')->count();
        $boundaryMatchCount = FabricRoll::where('is_boundary_match', true)->count();

        // Group distribution (top 10 groups by roll count)
        $groupDistribution = FabricRoll::whereNotNull('matched_group')
            ->where('classification_status', 'MATCHED')
            ->select('matched_group', DB::raw('count(*) as count'))
            ->groupBy('matched_group')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Stage breakdown
        $stageDistribution = FabricRoll::select('stage', DB::raw('count(*) as count'))
            ->groupBy('stage')
            ->get();

        // Scatter plot points (up to 200 recent rolls for visualization)
        $scatterPoints = FabricRoll::select(
            'id', 'roll_number', 'stage', 'matched_group',
            'classification_status', 'is_boundary_match',
            'length_shrinkage_avg as x', 'width_shrinkage_avg as y'
        )
            ->orderBy('id', 'desc')
            ->limit(200)
            ->get();

        // Recent 5 rolls
        $recentRolls = FabricRoll::orderBy('id', 'desc')->limit(6)->get();

        return response()->json([
            'summary' => [
                'total_rolls' => $totalRolls,
                'total_chart_rules' => $totalChartRules,
                'matched_count' => $matchedCount,
                'no_match_count' => $noMatchCount,
                'multiple_matches_count' => $multipleMatchesCount,
                'pending_count' => $pendingCount,
                'boundary_match_count' => $boundaryMatchCount,
                'match_rate' => $totalRolls > 0 ? round(($matchedCount / $totalRolls) * 100, 1) : 0,
            ],
            'group_distribution' => $groupDistribution,
            'stage_distribution' => $stageDistribution,
            'scatter_points' => $scatterPoints,
            'recent_rolls' => $recentRolls,
        ]);
    }
}

