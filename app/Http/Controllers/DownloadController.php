<?php

namespace App\Http\Controllers;

use App\Models\Workspace;
use App\Services\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadController extends Controller
{
    public function __construct(
        private WorkspaceService $workspaceService
    ) {}

    /**
     * Download an export file
     */
    public function downloadExport(Request $request): BinaryFileResponse
    {
        $filename = $request->query('filename');
        
        if (!$filename) {
            abort(400, 'Nom de fichier manquant');
        }
        
        // Sécuriser le nom de fichier
        $filename = basename($filename);
        
        // Vérifier que le fichier existe
        $filePath = 'exports/' . $filename;
        
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'Fichier non trouvé');
        }
        
        // Récupérer le workspace actuel pour vérifier les permissions
        $workspace = $this->workspaceService->getCurrentWorkspace(Auth::user());
        
        if (!$workspace || !$workspace->canUserAccess(Auth::user(), 'view')) {
            abort(403, 'Accès non autorisé');
        }
        
        // Retourner le fichier en téléchargement
        $fullPath = Storage::disk('public')->path($filePath);
        
        return response()->download($fullPath, $filename, [
            'Content-Type' => $this->getContentType($filename),
        ]);
    }
    
    /**
     * Get content type based on file extension
     */
    private function getContentType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        return match($extension) {
            'csv' => 'text/csv',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
            'json' => 'application/json',
            default => 'application/octet-stream',
        };
    }
}
