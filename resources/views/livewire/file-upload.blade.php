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
                                name="file-upload" 
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
                :disabled="!$file || $uploading"
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
</div>
