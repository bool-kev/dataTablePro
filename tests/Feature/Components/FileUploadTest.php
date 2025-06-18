<?php

use App\Livewire\FileUpload;
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

describe('FileUpload Component', function () {
    describe('Component Rendering', function () {
        it('can render the file upload component', function () {
            Livewire::test(FileUpload::class)
                ->assertStatus(200)
                ->assertSee('Sélectionner un fichier');
        });

        it('shows supported file types', function () {
            Livewire::test(FileUpload::class)
                ->assertSee('CSV, XLSX, XLS');
        });

        it('displays upload progress indicator', function () {
            $component = Livewire::test(FileUpload::class);
            
            expect($component->get('progress'))->toBe(0);
            expect($component->get('uploading'))->toBe(false);
        });
    });

    describe('File Validation', function () {
        it('accepts valid CSV files', function () {
            $csvContent = "name,email,age\nJohn Doe,john@example.com,30";
            $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->assertHasNoErrors('file');
        });

        it('accepts valid Excel files', function () {
            $file = UploadedFile::fake()->create('test.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->assertHasNoErrors('file');
        });        it('rejects invalid file types', function () {
            $file = UploadedFile::fake()->create('test.pdf', 100);
            
            Livewire::test(FileUpload::class)
                ->set('file', $file)
                ->assertHasErrors(['file' => 'mimes']);
        });
    });

    describe('Error Handling', function () {
        it('shows specific error messages for different failure types', function () {
            $invalidFile = UploadedFile::fake()->create('test.txt', 100);
            
            Livewire::test(FileUpload::class)
                ->set('file', $invalidFile)
                ->assertHasErrors(['file' => 'mimes'])
                ->assertSee('Le fichier doit être au format CSV, XLSX, XLS ou TSV.');
        });
    });
});
