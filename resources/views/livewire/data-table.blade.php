<div class="space-y-6">
    {{-- Barre de filtrage unifiée --}}
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex flex-col md:flex-row gap-4 flex-1 max-w-2xl">
            <div class="flex-shrink-0">
                <label class="block text-sm font-medium text-gray-700 mb-2">Rechercher dans</label>
                <select 
                    wire:model.live="filterColumn" 
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                >
                    <option value="all">Toutes les colonnes</option>
                    @foreach($availableColumns as $column)
                        <option value="{{ $column }}">{{ $column }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    @if($filterColumn === 'all')
                        Recherche globale
                    @else
                        Rechercher dans "{{ $filterColumn }}"
                    @endif
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        wire:model.live="filterValue" 
                        placeholder="@if($filterColumn === 'all') Rechercher dans toutes les colonnes... @else Rechercher dans {{ $filterColumn }}... @endif"
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                    @if(!empty($filterValue))
                        <button 
                            wire:click="clearFilter" 
                            class="px-3 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 flex items-center"
                            title="Effacer le filtre"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="flex gap-2">
            @if(count($selectedRows) > 0)
                <button 
                    wire:click="deleteSelected" 
                    wire:confirm="Êtes-vous sûr de vouloir supprimer les lignes sélectionnées ?"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors duration-200"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Supprimer ({{ count($selectedRows) }})
                </button>
            @endif
            
            <button 
                wire:click="exportCsv" 
                class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export CSV
            </button>
            
            <button 
                wire:click="exportExcel" 
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Export Excel
            </button>
            
            <button 
                wire:click="exportJson" 
                class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors duration-200"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                Export JSON
            </button>
        </div>
    </div>

    {{-- Messages flash --}}
    @if (session()->has('message'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left">
                        <input 
                            type="checkbox" 
                            wire:model.live="selectAll"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        >
                    </th>
                    @if (! in_array('id', $columns))
                        <th wire:click="sortBy('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            ID
                            @if($sortBy === 'id')
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                    @endif
                    @foreach($columns as $index => $column)
                        <th wire:click="sortByColumn('{{ $column }}')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                            {{ $column }}
                            @if($sortBy === $column)
                                <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                    @endforeach
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($data as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <input 
                                type="checkbox" 
                                wire:model.live="selectedRows" 
                                value="{{ $row->id }}"
                                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            >
                        </td>
                        @if ( ! in_array('id', $columns))
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $row->id }}
                            </td>
                        @endif
                        {{-- Dynamically render columns --}}
                        @foreach($columns as $column)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($editingRow && $editingRow->id === $row->id)
                                    <input 
                                        type="text" 
                                        wire:model="editData.{{ $column }}" 
                                        class="w-full px-2 py-1 border border-gray-300 rounded"
                                    >
                                @else
                                    {{ Str::limit($row->data[$column] ?? '', 50) }}
                                @endif
                            </td>
                        @endforeach
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($editingRow && $editingRow->id === $row->id)
                                <div class="flex items-center space-x-2">
                                    <button 
                                        wire:click="saveEdit" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-green-600 hover:text-white hover:bg-green-600 rounded-full transition-colors duration-200 cursor-pointer"
                                        title="Sauvegarder"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="cancelEdit" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-gray-600 hover:text-white hover:bg-gray-600 rounded-full transition-colors duration-200 cursor-pointer"
                                        title="Annuler"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @else
                                <div class="flex items-center space-x-1">
                                    <button 
                                        wire:click="viewRow({{ $row->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-blue-600 hover:text-white hover:bg-blue-600 rounded-full transition-colors duration-200 cursor-pointer"
                                        title="Voir les détails"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="editRow({{ $row->id }})" 
                                        class="inline-flex items-center justify-center w-8 h-8 text-indigo-600 hover:text-white hover:bg-indigo-600 rounded-full transition-colors duration-200 cursor-pointer"
                                        title="Éditer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <button 
                                        wire:click="deleteRow({{ $row->id }})" 
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer cette ligne ?"
                                        class="inline-flex items-center justify-center w-8 h-8 text-red-600 hover:text-black hover:bg-red-600 rounded-full transition-colors duration-200 cursor-pointer"
                                        title="Supprimer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 3 }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                            Aucune donnée trouvée
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
            <label class="text-sm text-gray-700">Lignes par page:</label>
            <select wire:model.live="perPage" class="border border-gray-300 rounded px-3 py-1">
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
        
        <div>
            {{ $data->links() }}
        </div>
    </div>

    {{-- Modal de visualisation --}}
    @if($showModal)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Détails de la ligne</h3>
                        <button wire:click="$set('showModal', false)" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($modalData as $key => $value)
                            <div class="border-b border-gray-200 pb-2">
                                <dt class="text-sm font-medium text-gray-500">{{ $key }}</dt>
                                <dd class="mt-1 text-sm text-gray-900 break-all">{{ $value }}</dd>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
