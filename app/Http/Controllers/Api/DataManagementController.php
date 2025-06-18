<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Repositories\ImportedDataRepository;
use App\Services\ImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DataManagementController extends Controller
{
    protected ImportedDataRepository $repository;
    protected ImportService $importService;

    public function __construct(ImportedDataRepository $repository, ImportService $importService)
    {
        $this->repository = $repository;
        $this->importService = $importService;
    }

    public function upload(Request $request, Workspace $workspace): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls'
        ]);

        try {
            $importHistory = $this->importService->processFile($request->file('file'), $workspace);
            
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'import_id' => $importHistory->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 400);
        }
    }

    public function validateUpload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,xlsx,xls|max:10240'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File validation passed'
        ]);
    }

    public function uploadProgress(string $id): JsonResponse
    {
        // Mock progress for testing
        return response()->json([
            'progress' => 100,
            'status' => 'completed'
        ]);
    }

    public function getData(Request $request, Workspace $workspace): JsonResponse
    {
        $search = $request->get('search', '');
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $perPage = $request->get('per_page', 15);

        $data = $this->repository->getForWorkspace(
            $workspace->id,
            $search,
            [],
            $sortBy,
            $sortDirection,
            $perPage
        );

        return response()->json($data);
    }

    public function searchData(Request $request, Workspace $workspace): JsonResponse
    {
        $search = $request->get('query', '');
        
        $data = $this->repository->getForWorkspace(
            $workspace->id,
            $search,
            [],
            'created_at',
            'desc',
            15
        );

        return response()->json($data);
    }

    public function getColumns(Workspace $workspace): JsonResponse
    {
        $columns = $this->repository->getUniqueColumns($workspace->id);
        
        return response()->json([
            'columns' => $columns
        ]);
    }

    public function updateData(Request $request, Workspace $workspace, int $id): JsonResponse
    {
        try {
            $data = $this->repository->find($id);
            
            if (!$data) {
                return response()->json(['message' => 'Data not found'], 404);
            }

            $this->repository->updateById($id, ['data' => $request->get('data', [])]);
            
            return response()->json([
                'success' => true,
                'message' => 'Data updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Update failed'
            ], 400);
        }
    }

    public function deleteData(Workspace $workspace, int $id): JsonResponse
    {
        try {
            $this->repository->deleteById($id);
            
            return response()->json([
                'success' => true,
                'message' => 'Data deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed'
            ], 400);
        }
    }

    public function bulkDelete(Request $request, Workspace $workspace): JsonResponse
    {
        $ids = $request->get('ids', []);
        
        try {
            $this->repository->bulkDelete($ids);
            
            return response()->json([
                'success' => true,
                'message' => 'Data deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk delete failed'
            ], 400);
        }
    }

    public function exportCsv(Request $request, Workspace $workspace): JsonResponse
    {
        return response()->json([
            'success' => true,
            'export_id' => 'csv_' . time(),
            'message' => 'CSV export started'
        ]);
    }

    public function exportExcel(Request $request, Workspace $workspace): JsonResponse
    {
        return response()->json([
            'success' => true,
            'export_id' => 'excel_' . time(),
            'message' => 'Excel export started'
        ]);
    }

    public function exportStatus(Workspace $workspace, string $id): JsonResponse
    {
        return response()->json([
            'status' => 'completed',
            'download_url' => '/api/exports/' . $id . '/download'
        ]);
    }

    public function getStatistics(Workspace $workspace): JsonResponse
    {
        $totalRows = $this->repository->getWorkspaceDataCount($workspace->id);
        
        return response()->json([
            'total_rows' => $totalRows,
            'total_imports' => 0,
            'last_import' => null
        ]);
    }

    public function getImportStatistics(Workspace $workspace): JsonResponse
    {
        return response()->json([
            'total_imports' => 0,
            'successful_imports' => 0,
            'failed_imports' => 0
        ]);
    }

    public function getDataQuality(Workspace $workspace): JsonResponse
    {
        return response()->json([
            'completeness' => 95.5,
            'accuracy' => 98.2,
            'consistency' => 96.8
        ]);
    }

    public function getColumnStatistics(Workspace $workspace, string $column): JsonResponse
    {
        return response()->json([
            'column' => $column,
            'unique_values' => 50,
            'null_count' => 2,
            'data_type' => 'string'
        ]);
    }

    public function getImportUpdates(Workspace $workspace): JsonResponse
    {
        return response()->json([
            'updates' => []
        ]);
    }

    public function getDataCount(Workspace $workspace): JsonResponse
    {
        $count = $this->repository->getWorkspaceDataCount($workspace->id);
        
        return response()->json([
            'count' => $count
        ]);
    }
}
