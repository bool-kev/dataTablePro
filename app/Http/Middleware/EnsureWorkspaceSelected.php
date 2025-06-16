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
        \Log::info('EnsureWorkspaceSelected middleware called', ['route' => $request->route()?->getName()]);
        
        // Vérifier si l'utilisateur est authentifié
        if (!auth()->check()) {
            \Log::info('User not authenticated, passing through');
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
        \Log::info('Checking workspace selection', ['workspace_id' => $workspaceId, 'user_id' => auth()->id()]);
        
        if (!$workspaceId) {
            \Log::info('No workspace in session, trying to auto-select');
            // Essayer de récupérer le premier workspace de l'utilisateur
            $workspace = auth()->user()->workspaces()->first();
            
            if ($workspace) {
                \Log::info('Auto-selecting workspace', ['workspace_id' => $workspace->id]);
                session(['current_workspace_id' => $workspace->id]);
                $workspace->setupDatabaseConnection();
            } else {
                \Log::warning('No workspace found for user', ['user_id' => auth()->id()]);
                // Rediriger vers la création de workspace
                return redirect()->route('create-workspace')
                    ->with('warning', 'Vous devez créer ou sélectionner un workspace pour continuer.');
            }
        } else {
            \Log::info('Workspace found in session', ['workspace_id' => $workspaceId]);
            // Configurer la connexion à la base de données du workspace
            $workspace = auth()->user()->workspaces()->find($workspaceId);
            
            if ($workspace) {
                \Log::info('Setting up workspace database connection', ['workspace_id' => $workspace->id]);
                $workspace->setupDatabaseConnection();
            } else {
                \Log::error('Workspace not found, clearing session', ['workspace_id' => $workspaceId]);
                session()->forget('current_workspace_id');
                return redirect()->route('workspaces')
                    ->with('error', 'Le workspace sélectionné n\'existe plus.');
            }
        }

        \Log::info('Middleware completed, proceeding to next');
        return $next($request);
    }
}
