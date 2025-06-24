<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Importer un fichier</h2>
        <p class="text-sm text-gray-600">Sélectionnez un fichier CSV, XLSX ou XLS à importer (max 10MB)</p>
    </div>

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

    <form wire:submit="upload">
        {{-- Zone de drop de fichier --}}
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Fichier à importer
            </label>
            
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    
                    <div class="flex text-sm text-gray-600">
                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <span>Sélectionner un fichier</span>
                            <input 
                                id="file-upload" 
                                type="file" 
                                class="sr-only" 
                                wire:model="file"
                                accept=".csv,.xlsx,.xls"
                            >
                        </label>
                        <p class="pl-1">ou glisser-déposer</p>
                    </div>
                    
                    <p class="text-xs text-gray-500">
                        CSV, XLSX, XLS jusqu'à 10MB
                    </p>
                </div>
            </div>
            
            @if($file)
                <div class="mt-2 text-sm text-gray-600">
                    <strong>Fichier sélectionné:</strong> {{ $file->getClientOriginalName() }}
                    <span class="text-gray-500">({{ number_format($file->getSize() / 1024, 2) }} KB)</span>
                </div>
            @endif
            
            @error('file') 
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p> 
            @enderror
        </div>

        {{-- Barre de progression --}}
        @if($uploading)
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progression de l'import</span>
                    <span>{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: {{ $progress }}%"></div>
                </div>
                <div class="mt-2 text-sm text-gray-600 flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Traitement en cours...
                </div>
            </div>
        @endif

        {{-- Boutons --}}
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-500">
                Formats supportés: CSV, XLSX, XLS
            </div>
            
            <button 
                type="submit" 
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                @disabled(!$file || $uploading)
            >
                @if($uploading)
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Importation...
                @else
                    <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Importer le fichier
                @endif
            </button>
        </div>
    </form>

    {{-- Informations d'aide --}}
    <div class="mt-6 p-4 bg-blue-50 rounded-md">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">
                    Conseils d'import
                </h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Assurez-vous que votre fichier contient des en-têtes dans la première ligne</li>
                        <li>Les lignes vides seront automatiquement ignorées</li>
                        <li>Les caractères spéciaux sont supportés (UTF-8)</li>
                        <li>L'import peut prendre du temps pour les gros fichiers</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Statistiques d'Import --}}
    @if($showStatsModal && $importStats)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" id="stats-modal">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="mt-3">
                    {{-- Header du modal --}}
                    <div class="flex items-center justify-between pb-4 border-b">
                        <h3 class="text-xl font-semibold text-gray-900 flex items-center">
                            <svg class="mr-3 h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Import terminé avec succès !
                        </h3>
                        <button wire:click="closeStatsModal" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Contenu du modal --}}
                    <div class="mt-6 space-y-6">
                        {{-- Informations du fichier --}}
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h4 class="font-medium text-blue-900 mb-3 flex items-center">
                                <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Informations du fichier
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="font-medium text-gray-700">Nom du fichier:</span>
                                    <span class="text-gray-900">{{ $importStats['filename'] }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Taille:</span>
                                    <span class="text-gray-900">{{ number_format($importStats['filesize'] / 1024, 2) }} KB</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Workspace:</span>
                                    <span class="text-gray-900">{{ $importStats['workspace_name'] }}</span>
                                </div>
                                <div>
                                    <span class="font-medium text-gray-700">Date d'import:</span>
                                    <span class="text-gray-900">{{ $importStats['import_date'] }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Statistiques principales --}}
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($importStats['total_rows']) }}</div>
                                <div class="text-sm text-gray-600">Lignes totales</div>
                            </div>
                            <div class="bg-green-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-green-600">{{ number_format($importStats['successful_rows']) }}</div>
                                <div class="text-sm text-gray-600">Réussies</div>
                            </div>
                            <div class="bg-red-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-red-600">{{ number_format($importStats['failed_rows']) }}</div>
                                <div class="text-sm text-gray-600">Échecs</div>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $importStats['success_rate'] }}%</div>
                                <div class="text-sm text-gray-600">Taux de réussite</div>
                            </div>
                        </div>

                        {{-- Barre de progression visuelle --}}
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex justify-between text-sm text-gray-600 mb-2">
                                <span>Progression de l'import</span>
                                <span>{{ $importStats['success_rate'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-gradient-to-r from-green-500 to-green-600 h-3 rounded-full" 
                                     style="width: {{ $importStats['success_rate'] }}%"></div>
                            </div>
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>{{ number_format($importStats['successful_rows']) }} réussies</span>
                                <span>{{ number_format($importStats['failed_rows']) }} erreurs</span>
                            </div>
                        </div>

                        {{-- Erreurs (si il y en a) --}}
                        @if($importStats['failed_rows'] > 0 && !empty($importStats['errors']))
                            <div class="bg-red-50 p-4 rounded-lg">
                                <h4 class="font-medium text-red-900 mb-3 flex items-center">
                                    <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    Détails des erreurs
                                </h4>
                                <div class="max-h-32 overflow-y-auto">
                                    <ul class="text-sm text-red-700 space-y-1">
                                        @foreach(array_slice($importStats['errors'], 0, 5) as $error)
                                            <li class="flex items-start">
                                                <span class="inline-block w-2 h-2 bg-red-400 rounded-full mt-1.5 mr-2 flex-shrink-0"></span>
                                                {{ $error }}
                                            </li>
                                        @endforeach
                                        @if(count($importStats['errors']) > 5)
                                            <li class="text-red-600 font-medium">
                                                ... et {{ count($importStats['errors']) - 5 }} autres erreurs
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Boutons d'action --}}
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t mt-6">
                        <button wire:click="closeStatsModal" 
                                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 text-sm font-medium rounded-md transition-colors">
                            Fermer
                        </button>
                        <button wire:click="viewData" 
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md transition-colors flex items-center">
                            <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            Voir mes données
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
