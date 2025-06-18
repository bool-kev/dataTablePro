<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DataManagementController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Data Management API Routes
Route::middleware(['auth:sanctum', 'workspace'])->prefix('workspaces/{workspace}')->group(function () {
    // File Upload Endpoints
    Route::post('/upload', [DataManagementController::class, 'upload'])->name('api.upload');
    Route::post('/upload/validate', [DataManagementController::class, 'validateUpload'])->name('api.upload.validate');
    Route::get('/upload/progress/{id}', [DataManagementController::class, 'uploadProgress'])->name('api.upload.progress');

    // Data Retrieval Endpoints
    Route::get('/data', [DataManagementController::class, 'getData'])->name('api.data.index');
    Route::get('/data/search', [DataManagementController::class, 'searchData'])->name('api.data.search');
    Route::get('/data/columns', [DataManagementController::class, 'getColumns'])->name('api.data.columns');

    // Data Manipulation Endpoints
    Route::put('/data/{id}', [DataManagementController::class, 'updateData'])->name('api.data.update');
    Route::delete('/data/{id}', [DataManagementController::class, 'deleteData'])->name('api.data.delete');
    Route::delete('/data', [DataManagementController::class, 'bulkDelete'])->name('api.data.bulk-delete');

    // Export Endpoints
    Route::post('/export/csv', [DataManagementController::class, 'exportCsv'])->name('api.export.csv');
    Route::post('/export/excel', [DataManagementController::class, 'exportExcel'])->name('api.export.excel');
    Route::get('/exports/{id}', [DataManagementController::class, 'exportStatus'])->name('api.export.status');

    // Statistics and Analytics Endpoints
    Route::get('/statistics', [DataManagementController::class, 'getStatistics'])->name('api.statistics');
    Route::get('/statistics/imports', [DataManagementController::class, 'getImportStatistics'])->name('api.statistics.imports');
    Route::get('/statistics/data-quality', [DataManagementController::class, 'getDataQuality'])->name('api.statistics.data-quality');
    Route::get('/statistics/columns/{column}', [DataManagementController::class, 'getColumnStatistics'])->name('api.statistics.column');

    // Real-time Features
    Route::get('/import-updates', [DataManagementController::class, 'getImportUpdates'])->name('api.import-updates');
    Route::get('/data-count', [DataManagementController::class, 'getDataCount'])->name('api.data-count');
});

// Error handling routes
Route::fallback(function () {
    return response()->json(['message' => 'API endpoint not found'], 404);
});
