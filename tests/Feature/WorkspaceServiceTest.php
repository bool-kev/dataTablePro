<?php

use App\Models\User;
use App\Models\Workspace;
use App\Services\WorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->workspaceService = app(WorkspaceService::class);
});

it('can create a new workspace', function () {
    $workspace = $this->workspaceService->createWorkspace($this->user, [
        'name' => 'Test Workspace',
        'description' => 'A test workspace',
        'database_type' => 'sqlite',
    ]);

    expect($workspace)->toBeInstanceOf(Workspace::class);
    expect($workspace->name)->toBe('Test Workspace');
    expect($workspace->owner_id)->toBe($this->user->id);
    expect($workspace->database_type)->toBe('sqlite');
    
    // Vérifier que l'utilisateur est ajouté au workspace
    expect($workspace->users()->where('user_id', $this->user->id)->exists())->toBeTrue();
});

it('can switch to a workspace', function () {
    $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $workspace->users()->attach($this->user->id, ['role' => 'owner']);

    $result = $this->workspaceService->switchWorkspace($this->user, $workspace);

    expect($result)->toBeTrue();
    expect(session('current_workspace_id'))->toBe($workspace->id);
});

it('cannot switch to a workspace without access', function () {
    $otherUser = User::factory()->create();
    $workspace = Workspace::factory()->create(['owner_id' => $otherUser->id]);

    $result = $this->workspaceService->switchWorkspace($this->user, $workspace);

    expect($result)->toBeFalse();
    expect(session('current_workspace_id'))->toBeNull();
});

it('can delete a workspace', function () {
    $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);

    $result = $this->workspaceService->deleteWorkspace($workspace);

    expect($result)->toBeTrue();
    expect(Workspace::find($workspace->id))->toBeNull();
});

it('can invite user to workspace', function () {
    $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $invitedUser = User::factory()->create();

    $result = $this->workspaceService->inviteUserToWorkspace(
        $workspace, 
        $invitedUser->email, 
        'editor'
    );

    expect($result)->toBeTrue();
    expect($workspace->users()->where('user_id', $invitedUser->id)->exists())->toBeTrue();
    
    $pivot = $workspace->users()->where('user_id', $invitedUser->id)->first()->pivot;
    expect($pivot->role)->toBe('editor');
});

it('cannot invite non-existent user', function () {
    $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);

    $result = $this->workspaceService->inviteUserToWorkspace(
        $workspace, 
        'nonexistent@example.com', 
        'editor'
    );

    expect($result)->toBeFalse();
});

it('can get current workspace', function () {
    $workspace = Workspace::factory()->create(['owner_id' => $this->user->id]);
    $workspace->users()->attach($this->user->id, ['role' => 'owner']);
    session(['current_workspace_id' => $workspace->id]);

    $currentWorkspace = $this->workspaceService->getCurrentWorkspace($this->user);

    expect($currentWorkspace)->toBeInstanceOf(Workspace::class);
    expect($currentWorkspace->id)->toBe($workspace->id);
});
