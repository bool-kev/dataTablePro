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
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($import->status) }}
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
                                    <button 
                                        wire:click="viewDetails({{ $import->id }})"
                                        class="text-indigo-600 hover:text-indigo-900"
                                    >
                                        {{ $showDetails === $import->id ? 'Masquer' : 'Détails' }}
                                    </button>
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
</div>
