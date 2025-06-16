<div class="space-y-6">
    {{-- En-tête --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mes Workspaces</h1>
            <p class="mt-1 text-sm text-gray-600">
                Gérez vos espaces de travail et leurs collaborateurs
            </p>
        </div>
        <a 
            href="{{ route('create-workspace') }}" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            wire:navigate
        >
            <flux:icon.plus class="-ml-1 mr-2 h-4 w-4" />
            Nouveau workspace
        </a>
    </div>

    {{-- Messages flash --}}
    @if (session()->has('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            {{ session('error') }}
        </div>
    @endif

    {{-- Barre de recherche --}}
    <div class="flex items-center space-x-4">
        <div class="flex-1 max-w-md">
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Rechercher un workspace..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
        </div>
    </div>

    {{-- Grille des workspaces --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($workspaces as $workspace)
            <div class="bg-white rounded-lg shadow border border-gray-200 hover:shadow-md transition-shadow">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center">
                                <flux:icon.folder class="h-5 w-5 text-gray-400 mr-2" />
                                <h3 class="text-lg font-medium text-gray-900 truncate">
                                    {{ $workspace->name }}
                                </h3>
                            </div>
                            @if($workspace->description)
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">
                                    {{ $workspace->description }}
                                </p>
                            @endif
                            
                            <div class="mt-3 flex items-center text-xs text-gray-500">
                                <span>{{ ucfirst($workspace->database_type) }}</span>
                                <span class="mx-2">•</span>
                                <span>Créé le {{ $workspace->created_at->format('d/m/Y') }}</span>
                            </div>
                        </div>
                        
                        <div class="ml-3">
                            <div class="flex items-center space-x-1">
                                {{-- Menu déroulant --}}
                                <div class="relative" x-data="{ open: false }">
                                    <button 
                                        @click="open = !open"
                                        class="p-1 rounded-full hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        <flux:icon.ellipsis-vertical class="h-4 w-4 text-gray-400" />
                                    </button>
                                    
                                    <div 
                                        x-show="open" 
                                        @click.away="open = false"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                        class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-10"
                                    >
                                        <div class="py-1" role="menu">
                                            <button 
                                                wire:click="switchToWorkspace({{ $workspace->id }})"
                                                @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                <flux:icon.arrow-right class="mr-3 h-4 w-4" />
                                                Basculer vers ce workspace
                                            </button>
                                            <button 
                                                wire:click="openSettings({{ $workspace->id }})"
                                                @click="open = false"
                                                class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                            >
                                                <flux:icon.settings class="mr-3 h-4 w-4" />
                                                Paramètres
                                            </button>
                                            @if($workspace->owner_id === auth()->id())
                                                <button 
                                                    wire:click="confirmDelete({{ $workspace->id }})"
                                                    @click="open = false"
                                                    class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50"
                                                >
                                                    <flux:icon.trash class="mr-3 h-4 w-4" />
                                                    Supprimer
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- Statistiques rapides --}}
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $workspace->users_count ?? 0 }} collaborateur(s)</span>
                            @if($workspace->owner_id === auth()->id())
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Propriétaire
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Membre
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    {{-- Action principale --}}
                    <div class="mt-4">
                        <button 
                            wire:click="switchToWorkspace({{ $workspace->id }})"
                            class="w-full inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <flux:icon.arrow-right class="mr-2 h-4 w-4" />
                            Ouvrir
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <flux:icon.folder class="mx-auto h-12 w-12 text-gray-400" />
                <h3 class="mt-2 text-sm font-medium text-gray-900">Aucun workspace</h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if($search)
                        Aucun workspace ne correspond à votre recherche.
                    @else
                        Commencez par créer votre premier workspace.
                    @endif
                </p>
                @if(!$search)
                    <div class="mt-6">
                        <a 
                            href="{{ route('create-workspace') }}" 
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            wire:navigate
                        >
                            <flux:icon.plus class="-ml-1 mr-2 h-4 w-4" />
                            Créer mon premier workspace
                        </a>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($workspaces->hasPages())
        <div class="mt-6">
            {{ $workspaces->links() }}
        </div>
    @endif

    {{-- Modal de paramètres --}}
    @if($showSettingsModal && $selectedWorkspace)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Paramètres - {{ $selectedWorkspace->name }}</h3>
                        <button wire:click="closeSettingsModal" class="text-gray-400 hover:text-gray-600">
                            <flux:icon.x class="h-6 w-6" />
                        </button>
                    </div>

                    <form wire:submit="updateWorkspace" class="space-y-4">
                        <div>
                            <label for="editingName" class="block text-sm font-medium text-gray-700">Nom</label>
                            <input 
                                type="text" 
                                id="editingName"
                                wire:model="editingName" 
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            >
                            @error('editingName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="editingDescription" class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea 
                                id="editingDescription"
                                wire:model="editingDescription" 
                                rows="3"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                            @error('editingDescription') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="flex justify-end space-x-3 pt-4">
                            <button 
                                type="button"
                                wire:click="closeSettingsModal"
                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                            >
                                Annuler
                            </button>
                            <button 
                                type="submit"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
                            >
                                Enregistrer
                            </button>
                        </div>
                    </form>

                    {{-- Section d'invitation d'utilisateurs --}}
                    @if($selectedWorkspace->owner_id === auth()->id())
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Inviter des collaborateurs</h4>
                            
                            <div class="flex space-x-3">
                                <div class="flex-1">
                                    <input 
                                        type="email" 
                                        wire:model="inviteEmail" 
                                        placeholder="email@exemple.com"
                                        class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                    @error('inviteEmail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                
                                <div>
                                    <select 
                                        wire:model="inviteRole"
                                        class="block px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    >
                                        <option value="viewer">Lecteur</option>
                                        <option value="editor">Éditeur</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                
                                <button 
                                    wire:click="inviteUser"
                                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700"
                                >
                                    Inviter
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de confirmation de suppression --}}
    @if($showDeleteModal && $selectedWorkspace)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <flux:icon.exclamation-triangle class="mx-auto h-12 w-12 text-red-400" />
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Supprimer le workspace</h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500">
                            Êtes-vous sûr de vouloir supprimer "{{ $selectedWorkspace->name }}" ? 
                            Cette action est irréversible et supprimera toutes les données.
                        </p>
                    </div>
                    <div class="flex justify-center space-x-3 mt-6">
                        <button 
                            wire:click="$set('showDeleteModal', false)"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Annuler
                        </button>
                        <button 
                            wire:click="deleteWorkspace"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700"
                        >
                            Supprimer définitivement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
