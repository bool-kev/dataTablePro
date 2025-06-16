<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    @if($currentWorkspace)
        <button 
            @click="open = !open" 
            type="button" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <flux:icon.folder class="w-4 h-4 mr-2" />
            {{ $currentWorkspace->name }}
            <flux:icon.chevron-down class="ml-2 w-4 h-4" ::class="{ 'rotate-180': open }" />
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
            class="absolute z-50 mt-2 w-80 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5"
        >
            <div class="py-1" role="menu">
                <div class="px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wide border-b border-gray-100">
                    Workspaces disponibles
                </div>
                
                @foreach($availableWorkspaces as $workspace)
                    <button 
                        wire:click="switchWorkspace({{ $workspace->id }})"
                        @click="open = false"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $currentWorkspace->id === $workspace->id ? 'bg-blue-50 text-blue-600' : '' }}"
                        role="menuitem"
                    >
                        <div class="flex items-center flex-1">
                            <flux:icon.folder class="w-4 h-4 mr-3 {{ $currentWorkspace->id === $workspace->id ? 'text-blue-500' : 'text-gray-400' }}" />
                            <div class="text-left">
                                <div class="font-medium">{{ $workspace->name }}</div>
                                @if($workspace->description)
                                    <div class="text-xs text-gray-500 truncate">{{ $workspace->description }}</div>
                                @endif
                            </div>
                        </div>
                        @if($currentWorkspace->id === $workspace->id)
                            <flux:icon.check class="w-4 h-4 text-blue-500" />
                        @endif
                    </button>
                @endforeach
                
                <div class="border-t border-gray-100 mt-1">
                    <a 
                        href="{{ route('workspaces') }}" 
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        wire:navigate
                    >
                        <flux:icon.settings class="w-4 h-4 mr-3 text-gray-400" />
                        Gérer les workspaces
                    </a>
                    <a 
                        href="{{ route('create-workspace') }}" 
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        wire:navigate
                    >
                        <flux:icon.plus class="w-4 h-4 mr-3 text-gray-400" />
                        Créer un workspace
                    </a>
                </div>
            </div>
        </div>
    @else
        <a 
            href="{{ route('create-workspace') }}" 
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm bg-blue-600 text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            wire:navigate
        >
            <flux:icon.plus class="w-4 h-4 mr-2" />
            Créer un workspace
        </a>
    @endif
</div>
