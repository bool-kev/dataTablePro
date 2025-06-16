<?php

use App\Http\Controllers\LandingController;
use App\Livewire\CreateWorkspace;
use App\Livewire\Dashboard;
use App\Livewire\DataTable;
use App\Livewire\FileUpload;
use App\Livewire\ImportHistory;
use App\Livewire\WorkspaceManager;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('home');

// Routes DataTable - nécessitent un workspace
Route::middleware(['workspace', 'auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/data-table/{workspace?}', DataTable::class)->name('data-table');
    Route::get('/upload', FileUpload::class)->name('upload');
    Route::get('/import-history', ImportHistory::class)->name('import-history');
});

// Routes Workspace - pas besoin de workspace sélectionné
Route::get('/workspaces', WorkspaceManager::class)->name('workspaces');
Route::get('/create-workspace', CreateWorkspace::class)->name('create-workspace');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
