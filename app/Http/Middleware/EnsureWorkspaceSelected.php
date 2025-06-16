<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            return $next($request);
        }

        // Routes qui n'ont pas besoin d'un workspace
        $excludedRoutes = [
            'workspaces',
            'create-workspace',
            'settings.*',
        ];

        foreach ($excludedRoutes as $route) {
            if ($request->routeIs($route)) {
                return $next($request);
            }
        }

        // Vérifier si un workspace est sélectionné
        $workspaceId = session('current_workspace_id');
        
        if (!$workspaceId) {
            // Essayer de récupérer le premier workspace de l'utilisateur
            $workspace = auth()->user()->workspaces()->first();
            
            if ($workspace) {
                session(['current_workspace_id' => $workspace->id]);
                $workspace->setupDatabaseConnection();
            } else {
                // Rediriger vers la création de workspace
                return redirect()->route('create-workspace')
                    ->with('warning', 'Vous devez créer ou sélectionner un workspace pour continuer.');
            }
        } else {
            // Configurer la connexion à la base de données du workspace
            $workspace = auth()->user()->workspaces()->find($workspaceId);
            
            if ($workspace) {
                $workspace->setupDatabaseConnection();
            } else {
                session()->forget('current_workspace_id');
                return redirect()->route('workspaces')
                    ->with('error', 'Le workspace sélectionné n\'existe plus.');
            }
        }

        return $next($request);
    }
}
