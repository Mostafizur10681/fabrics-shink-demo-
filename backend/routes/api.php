<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FabricRollController;
use App\Http\Controllers\Api\ShrinkageChartController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Health check
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'service' => 'Fabric Shrinkage Management API',
        'timestamp' => now()->toISOString(),
    ]);
});

// Dashboard
Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

// Fabric Rolls
Route::prefix('fabric-rolls')->group(function () {
    Route::get('/', [FabricRollController::class, 'index']);
    Route::post('/', [FabricRollController::class, 'store']);
    Route::post('/preview', [FabricRollController::class, 'previewClassification']);
    Route::post('/classify-all', [FabricRollController::class, 'classifyAll']);
    Route::get('/export', [FabricRollController::class, 'export']);
    Route::get('/{id}', [FabricRollController::class, 'show']);
    Route::put('/{id}', [FabricRollController::class, 'update']);
    Route::delete('/{id}', [FabricRollController::class, 'destroy']);
});

// Shrinkage Charts
Route::prefix('shrinkage-charts')->group(function () {
    Route::get('/', [ShrinkageChartController::class, 'index']);
    Route::post('/', [ShrinkageChartController::class, 'store']);
    Route::get('/sample-csv', [ShrinkageChartController::class, 'downloadSampleCsv']);
    Route::post('/seed-sample', [ShrinkageChartController::class, 'seedSample']);
    Route::post('/import', [ShrinkageChartController::class, 'import']);
    Route::get('/export', [ShrinkageChartController::class, 'export']);
    Route::get('/{id}', [ShrinkageChartController::class, 'show']);
    Route::put('/{id}', [ShrinkageChartController::class, 'update']);
    Route::delete('/{id}', [ShrinkageChartController::class, 'destroy']);
});
