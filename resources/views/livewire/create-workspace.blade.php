<div class="max-w-2xl mx-auto">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-900">Créer un nouveau workspace</h2>
            <p class="mt-1 text-sm text-gray-600">
                Un workspace vous permet d'organiser vos données de manière isolée avec sa propre base de données.
            </p>
        </div>

        <form wire:submit="createWorkspace" class="p-6">
            {{-- Messages flash --}}
            @if (session()->has('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                    {{ session('error') }}
                </div>
            @endif

            <div class="space-y-6">
                {{-- Nom du workspace --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Nom du workspace *
                    </label>
                    <div class="mt-1">
                        <input 
                            type="text" 
                            id="name"
                            wire:model="name" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-300 @enderror"
                            placeholder="Mon workspace"
                            required
                            {{ $isCreating ? 'disabled' : '' }}
                        >
                    </div>
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description --}}
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                        Description
                    </label>
                    <div class="mt-1">
                        <textarea 
                            id="description"
                            wire:model="description" 
                            rows="3"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('description') border-red-300 @enderror"
                            placeholder="Description de votre workspace..."
                            {{ $isCreating ? 'disabled' : '' }}
                        ></textarea>
                    </div>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Type de base de données --}}
                <div>
                    <label for="database_type" class="block text-sm font-medium text-gray-700">
                        Type de base de données *
                    </label>
                    <div class="mt-1">
                        <select 
                            id="database_type"
                            wire:model="database_type" 
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('database_type') border-red-300 @enderror"
                            {{ $isCreating ? 'disabled' : '' }}
                        >
                            <option value="sqlite">SQLite (Recommandé)</option>
                            <option value="mysql">MySQL</option>
                            <option value="postgresql">PostgreSQL</option>
                        </select>
                    </div>
                    @error('database_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">
                        SQLite est recommandé pour sa simplicité et ses performances pour les projets de taille moyenne.
                    </p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-8 flex items-center justify-between">
                <a 
                    href="{{ route('workspaces') }}" 
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    wire:navigate
                    {{ $isCreating ? 'disabled' : '' }}
                >
                    Annuler
                </a>

                <div class="flex space-x-3">
                    <button 
                        type="button"
                        wire:click="resetForm"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        {{ $isCreating ? 'disabled' : '' }}
                    >
                        Réinitialiser
                    </button>
                    
                    <button 
                        type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ $isCreating ? 'disabled' : '' }}
                    >
                        @if($isCreating)
                            <flux:icon.spinner class="animate-spin -ml-1 mr-2 h-4 w-4" />
                            Création en cours...
                        @else
                            <flux:icon.plus class="-ml-1 mr-2 h-4 w-4" />
                            Créer le workspace
                        @endif
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Informations supplémentaires --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <flux:icon.info class="h-5 w-5 text-blue-400" />
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">À propos des workspaces</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Chaque workspace dispose de sa propre base de données isolée</li>
                        <li>Vous pouvez inviter d'autres utilisateurs à collaborer</li>
                        <li>Les données importées sont automatiquement organisées par workspace</li>
                        <li>Vous pouvez basculer entre vos workspaces à tout moment</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
