<div class="space-y-6">
    {{-- En-tête avec sélecteur de workspace --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            @if($currentWorkspace)
                <p class="mt-1 text-sm text-gray-600">
                    Workspace: <span class="font-medium">{{ $currentWorkspace->name }}</span>
                </p>
            @endif
        </div>
        
        @if($currentWorkspace)
            <livewire:workspace-selector />
        @endif
    </div>

    @if(!$currentWorkspace)
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
            </svg>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Aucun workspace sélectionné</h3>
            <p class="mt-1 text-sm text-gray-500">
                Créez ou sélectionnez un workspace pour voir vos données.
            </p>
            <div class="mt-6">
                <a 
                    href="{{ route('create-workspace') }}" 
                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                    wire:navigate
                >
                    <svg class="-ml-1 mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Créer un workspace
                </a>
            </div>
        </div>
    @else
        {{-- Statistiques principales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Total des lignes
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ number_format($stats['total_rows']) }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Imports réussis
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['successful_imports'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Imports échoués
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['failed_imports'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Colonnes uniques
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['unique_columns'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Graphiques --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Graphique des imports par jour --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Imports par jour (7 derniers jours)</h3>
                <div class="h-64" id="imports-chart">
                    <canvas id="importsChart"></canvas>
                </div>
            </div>

            {{-- Graphique de répartition des types de fichiers --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Répartition des types de fichiers</h3>
                <div class="h-64" id="file-types-chart">
                    <canvas id="fileTypesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Derniers imports et accès rapides --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Derniers imports --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Derniers imports</h3>
                </div>
                <div class="p-6">
                    @if($recentImports->count() > 0)
                        <ul class="space-y-3">
                            @foreach($recentImports as $import)
                                <li class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0">
                                            @if($import->status === 'completed')
                                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @elseif($import->status === 'failed')
                                                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @else
                                                <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ $import->original_filename }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ $import->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $import->total_rows ?? 0 }} lignes
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                        <div class="mt-4">
                            <a 
                                href="{{ route('import-history') }}" 
                                class="text-sm text-blue-600 hover:text-blue-500"
                                wire:navigate
                            >
                                Voir tout l'historique →
                            </a>
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Aucun import récent</p>
                    @endif
                </div>
            </div>

            {{-- Accès rapides --}}
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Accès rapides</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a 
                        href="{{ route('upload') }}" 
                        class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                        wire:navigate
                    >
                        <svg class="h-5 w-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Importer des fichiers</p>
                            <p class="text-xs text-gray-500">CSV, Excel (XLSX, XLS)</p>
                        </div>
                    </a>

                    <a 
                        href="{{ route('data-table') }}" 
                        class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                        wire:navigate
                    >
                        <svg class="h-5 w-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Consulter les données</p>
                            <p class="text-xs text-gray-500">Table interactive avec filtres</p>
                        </div>
                    </a>

                    <a 
                        href="{{ route('workspaces') }}" 
                        class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
                        wire:navigate
                    >
                        <svg class="h-5 w-5 text-purple-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Gérer les workspaces</p>
                            <p class="text-xs text-gray-500">Créer, modifier, partager</p>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- Scripts pour les graphiques --}}
@if($currentWorkspace)
    @push('scripts')
    <script wire:ignore src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script wire:ignore>
        document.addEventListener('DOMContentLoaded', function() {
            // Graphique des imports par jour
            const importsCtx = document.getElementById('importsChart').getContext('2d');
            new Chart(importsCtx, {
                type: 'line',
                data: {
                    labels: @js($chartData['imports_by_day']['labels']),
                    datasets: [{
                        label: 'Imports',
                        data: @js($chartData['imports_by_day']['data']),
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Graphique des types de fichiers
            const fileTypesCtx = document.getElementById('fileTypesChart').getContext('2d');
            new Chart(fileTypesCtx, {
                type: 'doughnut',
                data: {
                    labels: @js($chartData['file_types']['labels']),
                    datasets: [{
                        data: @js($chartData['file_types']['data']),
                        backgroundColor: [
                            'rgb(59, 130, 246)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)',
                            'rgb(139, 92, 246)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
    </script>
    @endpush
@endif
