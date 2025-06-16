<div class="relative" x-data="{ open: @entangle('showDropdown') }">
    @if($currentWorkspace)
        <button 
            @click="open = !open" 
            type="button" 
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
            </svg>
            {{ $currentWorkspace->name }}
            <svg class="ml-2 w-4 h-4" ::class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
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
                            <svg class="w-4 h-4 mr-3 {{ $currentWorkspace->id === $workspace->id ? 'text-blue-500' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                            </svg>
                            <div class="text-left">
                                <div class="font-medium">{{ $workspace->name }}</div>
                                @if($workspace->description)
                                    <div class="text-xs text-gray-500 truncate">{{ $workspace->description }}</div>
                                @endif
                            </div>
                        </div>
                        @if($currentWorkspace->id === $workspace->id)
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif
                    </button>
                @endforeach
                
                <div class="border-t border-gray-100 mt-1">
                    <a 
                        href="{{ route('workspaces') }}" 
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        wire:navigate
                    >
                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Gérer les workspaces
                    </a>
                    <a 
                        href="{{ route('create-workspace') }}" 
                        class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        wire:navigate
                    >
                        <svg class="w-4 h-4 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
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
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Créer un workspace
        </a>
    @endif
</div>
