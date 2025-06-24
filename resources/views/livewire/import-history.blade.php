<div class="space-y-6">
    {{-- Statistiques globales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total des imports</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($statistics['total_imports']) }}</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Imports réussis</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($statistics['successful_imports']) }}</dd>
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
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17v4a2 2 0 002 2h4M13 13h4a2 2 0 012 2v4a2 2 0 01-2 2H9a2 2 0 01-2-2v-4a2 2 0 012-2h4z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Total lignes</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($statistics['total_rows']) }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-5">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-6 w-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Taux de réussite</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ number_format($statistics['success_rate'], 1) }}%</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historique des imports --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                Historique des imports
            </h3>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Fichier
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Lignes
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($imports as $import)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $import->original_filename }}</div>
                                    <div class="text-sm text-gray-500">{{ strtoupper($import->file_type) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                        @if($import->status === 'completed') bg-green-100 text-green-800
                                        @elseif($import->status === 'failed') bg-red-100 text-red-800
                                        @elseif($import->status === 'processing') bg-yellow-100 text-yellow-800
                                        @elseif($import->status === 'rolled_back') bg-orange-100 text-orange-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @if($import->status === 'rolled_back')
                                            Annulé
                                        @else
                                            {{ ucfirst($import->status) }}
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>Total: {{ number_format($import->total_rows) }}</div>
                                    <div class="text-xs text-gray-500">
                                        Réussies: {{ number_format($import->successful_rows) }} | 
                                        Échecs: {{ number_format($import->failed_rows) }}
                                    </div>
                                    @if($import->total_rows > 0)
                                        <div class="w-full bg-gray-200 rounded-full h-1 mt-1">
                                            <div class="bg-green-600 h-1 rounded-full" style="width: {{ $import->success_rate }}%"></div>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <div>{{ $import->created_at->format('d/m/Y H:i') }}</div>
                                    @if($import->completed_at)
                                        <div class="text-xs text-gray-500">
                                            Terminé: {{ $import->completed_at->format('H:i') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <button 
                                            wire:click="viewDetails({{ $import->id }})"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ $showDetails === $import->id ? 'Masquer' : 'Détails' }}
                                        </button>
                                        
                                        @if($import->status === 'completed' && $import->successful_rows > 0)
                                            <button 
                                                wire:click="viewImportData({{ $import->id }})"
                                                class="text-blue-600 hover:text-blue-900"
                                                title="Voir les données de cet import"
                                            >
                                                Données
                                            </button>
                                            
                                            <button 
                                                wire:click="confirmRollback({{ $import->id }})"
                                                class="text-red-600 hover:text-red-900"
                                                title="Annuler cet import"
                                            >
                                                Rollback
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            
                            {{-- Détails expandables --}}
                            @if($showDetails === $import->id)
                                <tr>
                                    <td colspan="5" class="px-6 py-4 bg-gray-50">
                                        <div class="space-y-4">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Informations générales</h4>
                                                    <dl class="space-y-1 text-sm">
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Fichier original:</dt>
                                                            <dd class="text-gray-900">{{ $import->original_filename }}</dd>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Type:</dt>
                                                            <dd class="text-gray-900">{{ strtoupper($import->file_type) }}</dd>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Démarré:</dt>
                                                            <dd class="text-gray-900">{{ $import->started_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</dd>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Terminé:</dt>
                                                            <dd class="text-gray-900">{{ $import->completed_at?->format('d/m/Y H:i:s') ?? 'N/A' }}</dd>
                                                        </div>
                                                    </dl>
                                                </div>
                                                
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Statistiques</h4>
                                                    <dl class="space-y-1 text-sm">
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Total lignes:</dt>
                                                            <dd class="text-gray-900">{{ number_format($import->total_rows) }}</dd>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Lignes réussies:</dt>
                                                            <dd class="text-green-600">{{ number_format($import->successful_rows) }}</dd>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Lignes échouées:</dt>
                                                            <dd class="text-red-600">{{ number_format($import->failed_rows) }}</dd>
                                                        </div>
                                                        <div class="flex justify-between">
                                                            <dt class="text-gray-500">Taux de réussite:</dt>
                                                            <dd class="text-gray-900">{{ number_format($import->success_rate, 2) }}%</dd>
                                                        </div>
                                                    </dl>
                                                </div>
                                            </div>
                                            
                                            {{-- Erreurs --}}
                                            @if($import->errors && count($import->errors) > 0)
                                                <div>
                                                    <h4 class="text-sm font-medium text-gray-900 mb-2">Erreurs ({{ count($import->errors) }})</h4>
                                                    <div class="bg-red-50 border border-red-200 rounded-md p-3 max-h-40 overflow-y-auto">
                                                        <ul class="text-sm text-red-700 space-y-1">
                                                            @foreach($import->errors as $error)
                                                                <li>• {{ $error }}</li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    Aucun import trouvé
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            <div class="mt-4">
                {{ $imports->links() }}
            </div>
        </div>
    </div>

    {{-- Modal de visualisation des données d'import --}}
    @if($showDataModal && $selectedImport && $selectedImportData)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="data-modal">
            <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-5/6 lg:w-4/5 xl:w-3/4 shadow-lg rounded-md bg-white max-h-screen overflow-y-auto">
                <div class="mt-3">
                    {{-- Header du modal --}}
                    <div class="flex items-center justify-between pb-4 border-b">
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                            <svg class="mr-3 h-6 w-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Données de l'import: {{ $selectedImport->original_filename }}
                        </h3>
                        <button wire:click="closeDataModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Informations de l'import --}}
                    <div class="mt-4 bg-blue-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Import ID:</span>
                                <span class="text-gray-900">#{{ $selectedImport->id }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Lignes:</span>
                                <span class="text-gray-900">{{ number_format($selectedImport->successful_rows) }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Date:</span>
                                <span class="text-gray-900">{{ $selectedImport->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Statut:</span>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst($selectedImport->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Données --}}
                    @if(count($selectedImportData) > 0)
                        <div class="mt-6">
                            <h4 class="text-lg font-medium text-gray-900 mb-4">
                                Données importées ({{ count($selectedImportData) }} lignes)
                            </h4>
                            
                            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                            @if(count($selectedImportData) > 0)
                                                @foreach(array_keys($selectedImportData[0]['data']) as $column)
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                        {{ $column }}
                                                    </th>
                                                @endforeach
                                            @endif
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date d'import</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach(array_slice($selectedImportData, 0, 50) as $row)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ $row['id'] }}</td>
                                                @foreach($row['data'] as $value)
                                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 max-w-xs truncate" title="{{ $value }}">
                                                        {{ $value }}
                                                    </td>
                                                @endforeach
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    {{ \Carbon\Carbon::parse($row['created_at'])->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            @if(count($selectedImportData) > 50)
                                <div class="mt-4 text-center text-sm text-gray-500">
                                    Affichage des 50 premières lignes sur {{ count($selectedImportData) }} au total.
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="mt-6 text-center text-gray-500">
                            Aucune donnée trouvée pour cet import.
                        </div>
                    @endif

                    {{-- Boutons d'action --}}
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                        <button wire:click="closeDataModal" 
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-md transition-colors">
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de confirmation de rollback --}}
    @if($showRollbackConfirm && $importToRollback)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="rollback-modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    {{-- Header du modal --}}
                    <div class="flex items-center justify-between pb-4 border-b">
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                            <svg class="mr-3 h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            Confirmer le rollback
                        </h3>
                        <button wire:click="closeRollbackConfirm" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Contenu du modal --}}
                    <div class="mt-6">
                        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">
                                        Attention : Cette action est irréversible !
                                    </h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>Vous êtes sur le point d'annuler l'import suivant :</p>
                                        <ul class="list-disc list-inside mt-2">
                                            <li><strong>Fichier :</strong> {{ $importToRollback->original_filename }}</li>
                                            <li><strong>Lignes importées :</strong> {{ number_format($importToRollback->successful_rows) }}</li>
                                            <li><strong>Date d'import :</strong> {{ $importToRollback->created_at->format('d/m/Y H:i') }}</li>
                                        </ul>
                                        <p class="mt-2 font-medium">
                                            Toutes les données de cet import seront définitivement supprimées de la base de données.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="text-gray-700 text-sm">
                            Êtes-vous sûr de vouloir continuer ? Cette action supprimera {{ number_format($importToRollback->successful_rows) }} ligne(s) de données.
                        </p>
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                        <button wire:click="closeRollbackConfirm" 
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-md transition-colors">
                            Annuler
                        </button>
                        <button wire:click="rollbackImport" 
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md transition-colors flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Confirmer le rollback
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
