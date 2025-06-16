@php
use App\Services\WorkspaceService;
use App\Models\Workspace;

$workspaceService = app(WorkspaceService::class);
$currentWorkspace = $workspaceService->getCurrentWorkspace(auth()->user());
@endphp

<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Workspace Collaboration') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if($currentWorkspace)
                <livewire:workspace-collaboration :workspace="$currentWorkspace" />
            @else
                <div class="bg-white shadow rounded-lg p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No workspace selected</h3>
                        <p class="mt-1 text-sm text-gray-500">You need to select or create a workspace to manage collaboration.</p>
                        <div class="mt-6">
                            <a href="{{ route('workspaces') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Manage Workspaces
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
