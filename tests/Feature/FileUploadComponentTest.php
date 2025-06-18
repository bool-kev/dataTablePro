<?php

use App\Livewire\FileUpload;
use App\Models\ImportHistory;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    
    // Créer un utilisateur et un workspace
    $this->user = User::factory()->create();
    $this->workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $this->workspace->users()->attach($this->user->id, ['role' => 'owner']);
    
    // Authentifier l'utilisateur et définir le workspace courant
    $this->actingAs($this->user);
    session(['current_workspace_id' => $this->workspace->id]);
});

it('can render the file upload component', function () {
    Livewire::test(FileUpload::class)
        ->assertStatus(200)
        ->assertSee('Sélectionner un fichier');
});

it('validates file type', function () {
    $file = UploadedFile::fake()->create('test.pdf', 100);
    
    Livewire::test(FileUpload::class)
        ->set('file', $file)
        ->assertHasErrors(['file' => 'mimes']);
});

it('can upload and process a valid Excel file', function () {
    $file = UploadedFile::fake()->create('test.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    
    // Note: This test would need a real Excel file for full testing
    // For now, we just test that the component accepts the file type
    Livewire::test(FileUpload::class)
        ->set('file', $file)
        ->assertHasNoErrors(['file']);
});
