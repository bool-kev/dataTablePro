<div class="space-y-6">
    {{-- Barre de recherche et filtres --}}
    <div class="flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="flex-1 max-w-md">
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Rechercher dans toutes les colonnes..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>
        
        <div class="flex gap-2">
            @if(count($selectedRows) > 0)
                <button 
                    wire:click="deleteSelected" 
                    wire:confirm="Êtes-vous sûr de vouloir supprimer les lignes sélectionnées ?"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700"
                >
                    Supprimer ({{ count($selectedRows) }})
                </button>
            @endif
            
            <button 
                wire:click="exportCsv" 
                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
            >
                Export CSV
            </button>
            
            <button 
                wire:click="exportExcel" 
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
            >
                Export Excel
            </button>
        </div>
    </div>

    {{-- Filtres par colonnes --}}
    @if(count($columns) > 0)
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
            @foreach($columns as $column)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $column }}</label>
                    <input 
                        type="text" 
                        wire:model.live="filters.{{ $column }}" 
                        placeholder="Filtrer {{ $column }}..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
            @endforeach
        </div>
    @endif

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
                    <th wire:click="sortBy('id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                        ID
                        @if($sortBy === 'id')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    @foreach($columns as $column)
                        <th wire:click="sortBy('{{ $column }}')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $row->id }}
                        </td>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            @if($editingRow && $editingRow->id === $row->id)
                                <button wire:click="saveEdit" class="text-green-600 hover:text-green-900">Sauver</button>
                                <button wire:click="cancelEdit" class="text-gray-600 hover:text-gray-900">Annuler</button>
                            @else
                                <button wire:click="viewRow({{ $row->id }})" class="text-blue-600 hover:text-blue-900">Voir</button>
                                <button wire:click="editRow({{ $row->id }})" class="text-indigo-600 hover:text-indigo-900">Éditer</button>
                                <button 
                                    wire:click="deleteRow({{ $row->id }})" 
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer cette ligne ?"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Supprimer
                                </button>
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
