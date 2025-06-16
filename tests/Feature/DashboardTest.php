<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $workspace = \App\Models\Workspace::factory()->create(['owner_id' => $user->id]);
    $workspace->users()->attach($user->id, ['role' => 'owner']);
    
    $this->actingAs($user);
    session(['current_workspace_id' => $workspace->id]);

    $this->get('/dashboard')->assertStatus(200);
});