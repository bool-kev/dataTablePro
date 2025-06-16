<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workspace>
 */
class WorkspaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company() . ' Workspace';
        
        return [
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . fake()->unique()->randomNumber(4),
            'description' => fake()->optional()->sentence(),
            'database_name' => 'workspace_' . fake()->unique()->slug(),
            'database_type' => 'sqlite',
            'owner_id' => \App\Models\User::factory(),
            'is_active' => true,
            'last_accessed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
