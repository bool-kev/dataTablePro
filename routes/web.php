<?php

use App\Http\Controllers\DownloadController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\WorkspaceInvitationController;
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

// Workspace invitation routes (public)
Route::prefix('invitation')->name('workspace.invitation.')->group(function () {
    Route::get('{token}', [WorkspaceInvitationController::class, 'show'])->name('show');
    Route::post('{token}/accept', [WorkspaceInvitationController::class, 'accept'])->name('accept');
    Route::post('{token}/decline', [WorkspaceInvitationController::class, 'decline'])->name('decline');
});

// Process invitation after login
Route::middleware('auth')->get('/process-invitation', [WorkspaceInvitationController::class, 'processAfterLogin'])->name('process.invitation');

// Routes DataTable - nécessitent un workspace
Route::middleware(['workspace', 'auth'])->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/data-table/{workspace?}', DataTable::class)->name('data-table');
    Route::get('/upload', FileUpload::class)->name('upload');
    Route::get('/import-history', ImportHistory::class)->name('import-history');
    Route::get('/collaboration', function() {
        return view('collaboration');
    })->name('collaboration');
});

// Routes Workspace - pas besoin de workspace sélectionné

Route::middleware(['auth'])->group(function () {
    Route::get('/workspaces', WorkspaceManager::class)->name('workspaces');
    Route::get('/create-workspace', CreateWorkspace::class)->name('create-workspace');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
