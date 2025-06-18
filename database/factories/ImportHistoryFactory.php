<?php

namespace Database\Factories;

use App\Models\ImportHistory;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImportHistory>
 */
class ImportHistoryFactory extends Factory
{
    protected $model = ImportHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->word() . '_' . fake()->dateFormat('Y-m-d_H-i-s') . '.csv';
        $originalFilename = fake()->word() . '.csv';
        
        return [
            'workspace_id' => Workspace::factory(),
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'file_path' => 'imports/' . $filename,
            'file_type' => fake()->randomElement(['csv', 'xlsx', 'xls']),
            'file_size' => fake()->numberBetween(1024, 10485760), // 1KB to 10MB
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'failed']),
            'total_rows' => fake()->numberBetween(0, 10000),
            'successful_rows' => function (array $attributes) {
                return fake()->numberBetween(0, $attributes['total_rows']);
            },
            'failed_rows' => function (array $attributes) {
                return $attributes['total_rows'] - $attributes['successful_rows'];
            },
            'error_message' => fake()->optional(0.2)->sentence(),
            'started_at' => fake()->optional(0.8)->dateTimeBetween('-1 week', 'now'),
            'completed_at' => fake()->optional(0.7)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Create a completed import
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $totalRows = fake()->numberBetween(100, 5000);
            $successfulRows = fake()->numberBetween(intval($totalRows * 0.8), $totalRows);
            
            return [
                'status' => 'completed',
                'total_rows' => $totalRows,
                'successful_rows' => $successfulRows,
                'failed_rows' => $totalRows - $successfulRows,
                'started_at' => fake()->dateTimeBetween('-1 week', '-1 hour'),
                'completed_at' => fake()->dateTimeBetween('-1 hour', 'now'),
                'error_message' => null,
            ];
        });
    }

    /**
     * Create a failed import
     */
    public function failed(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'total_rows' => 0,
                'successful_rows' => 0,
                'failed_rows' => 0,
                'started_at' => fake()->dateTimeBetween('-1 week', '-1 hour'),
                'completed_at' => fake()->dateTimeBetween('-1 hour', 'now'),
                'error_message' => fake()->randomElement([
                    'File format not supported',
                    'Database connection error',
                    'Invalid file encoding',
                    'File corrupted',
                    'Memory limit exceeded'
                ]),
            ];
        });
    }

    /**
     * Create a processing import
     */
    public function processing(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'processing',
                'total_rows' => fake()->numberBetween(1000, 10000),
                'successful_rows' => fake()->numberBetween(0, 500),
                'failed_rows' => fake()->numberBetween(0, 50),
                'started_at' => fake()->dateTimeBetween('-2 hours', 'now'),
                'completed_at' => null,
                'error_message' => null,
            ];
        });
    }

    /**
     * Create an import for CSV files
     */
    public function csv(): static
    {
        return $this->state(function (array $attributes) {
            $filename = fake()->word() . '_' . fake()->dateFormat('Y-m-d_H-i-s') . '.csv';
            
            return [
                'filename' => $filename,
                'original_filename' => fake()->word() . '.csv',
                'file_path' => 'imports/' . $filename,
                'file_type' => 'csv',
            ];
        });
    }

    /**
     * Create an import for Excel files
     */
    public function excel(): static
    {
        return $this->state(function (array $attributes) {
            $extension = fake()->randomElement(['xlsx', 'xls']);
            $filename = fake()->word() . '_' . fake()->dateFormat('Y-m-d_H-i-s') . '.' . $extension;
            
            return [
                'filename' => $filename,
                'original_filename' => fake()->word() . '.' . $extension,
                'file_path' => 'imports/' . $filename,
                'file_type' => $extension,
            ];
        });
    }
}
