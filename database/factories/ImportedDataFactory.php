<?php

namespace Database\Factories;

use App\Models\ImportedData;
use App\Models\ImportHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImportedData>
 */
class ImportedDataFactory extends Factory
{
    protected $model = ImportedData::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */    public function definition(): array
    {
        $data = [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'age' => fake()->numberBetween(18, 80),
            'phone' => fake()->optional(0.8)->phoneNumber(),
            'city' => fake()->optional(0.9)->city(),
            'country' => fake()->optional(0.9)->country(),
            'company' => fake()->optional(0.7)->company(),
            'job_title' => fake()->optional(0.7)->jobTitle(),
            'created_date' => fake()->optional(0.6)->dateTimeThisYear()->format('Y-m-d'),
            'unique_id' => fake()->unique()->uuid(), // Ajouter un ID unique pour éviter les collisions de hash
        ];

        // Retirer les clés null pour simuler des données réelles
        $data = array_filter($data, fn($value) => $value !== null);

        return [
            'import_history_id' => ImportHistory::factory(),
            'data' => $data,
            'row_hash' => md5(json_encode($data) . fake()->unique()->randomNumber()),
        ];
    }

    /**
     * Create customer data
     */    public function customer(): static
    {
        return $this->state(function (array $attributes) {
            $data = [
                'customer_id' => fake()->unique()->numberBetween(1000, 9999),
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'address' => fake()->address(),
                'city' => fake()->city(),
                'postal_code' => fake()->postcode(),
                'registration_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'status' => fake()->randomElement(['active', 'inactive', 'pending']),
                'total_orders' => fake()->numberBetween(0, 50),
                'total_spent' => fake()->randomFloat(2, 0, 5000),
                'unique_ref' => fake()->unique()->uuid(),
            ];

            return [
                'data' => $data,
                'row_hash' => md5(json_encode($data) . fake()->unique()->randomNumber()),
            ];
        });
    }

    /**
     * Create employee data
     */
    public function employee(): static
    {
        return $this->state(function (array $attributes) {
            $data = [
                'employee_id' => fake()->unique()->numberBetween(1000, 9999),
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'email' => fake()->unique()->companyEmail(),
                'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations']),
                'position' => fake()->jobTitle(),
                'hire_date' => fake()->dateTimeBetween('-5 years', 'now')->format('Y-m-d'),
                'salary' => fake()->numberBetween(30000, 120000),
                'manager_id' => fake()->optional(0.8)->numberBetween(1000, 9999),
                'office_location' => fake()->city(),
                'status' => fake()->randomElement(['active', 'on_leave', 'terminated']),
            ];

            return [
                'data' => $data,
                'row_hash' => md5(json_encode($data)),
            ];
        });
    }

    /**
     * Create product data
     */
    public function product(): static
    {
        return $this->state(function (array $attributes) {
            $data = [
                'product_id' => fake()->unique()->ean13(),
                'name' => fake()->words(3, true),
                'category' => fake()->randomElement(['Electronics', 'Clothing', 'Books', 'Home & Garden', 'Sports']),
                'brand' => fake()->company(),
                'price' => fake()->randomFloat(2, 10, 1000),
                'cost' => fake()->randomFloat(2, 5, 500),
                'stock_quantity' => fake()->numberBetween(0, 1000),
                'sku' => fake()->unique()->regexify('[A-Z]{3}-[0-9]{4}'),
                'description' => fake()->optional(0.8)->sentences(2, true),
                'launch_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                'status' => fake()->randomElement(['active', 'discontinued', 'out_of_stock']),
            ];

            return [
                'data' => $data,
                'row_hash' => md5(json_encode($data)),
            ];
        });
    }

    /**
     * Create sales data
     */
    public function sale(): static
    {
        return $this->state(function (array $attributes) {
            $data = [
                'order_id' => fake()->unique()->numberBetween(10000, 99999),
                'customer_id' => fake()->numberBetween(1000, 9999),
                'product_id' => fake()->ean13(),
                'quantity' => fake()->numberBetween(1, 10),
                'unit_price' => fake()->randomFloat(2, 10, 500),
                'total_amount' => fake()->randomFloat(2, 10, 5000),
                'discount' => fake()->optional(0.3)->randomFloat(2, 0, 100),
                'tax_amount' => fake()->randomFloat(2, 0, 200),
                'order_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
                'shipping_address' => fake()->address(),
                'status' => fake()->randomElement(['pending', 'processed', 'shipped', 'delivered', 'cancelled']),
                'payment_method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'cash']),
            ];

            return [
                'data' => $data,
                'row_hash' => md5(json_encode($data)),
            ];
        });
    }

    /**
     * Create financial data
     */
    public function financial(): static
    {
        return $this->state(function (array $attributes) {
            $data = [
                'transaction_id' => fake()->unique()->uuid(),
                'account_number' => fake()->bankAccountNumber(),
                'transaction_type' => fake()->randomElement(['debit', 'credit', 'transfer']),
                'amount' => fake()->randomFloat(2, -10000, 10000),
                'currency' => fake()->currencyCode(),
                'transaction_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s'),
                'description' => fake()->sentence(),
                'category' => fake()->randomElement(['salary', 'utilities', 'groceries', 'entertainment', 'investment']),
                'balance_after' => fake()->randomFloat(2, 0, 100000),
                'reference_number' => fake()->optional(0.7)->regexify('[A-Z0-9]{10}'),
            ];

            return [
                'data' => $data,
                'row_hash' => md5(json_encode($data)),
            ];
        });
    }

    /**
     * Create minimal data for performance tests
     */
    public function minimal(): static
    {
        return $this->state(function (array $attributes) {
            $data = [
                'id' => fake()->unique()->numberBetween(1, 1000000),
                'name' => fake()->name(),
                'value' => fake()->numberBetween(1, 100),
            ];

            return [
                'data' => $data,
                'row_hash' => md5(json_encode($data)),
            ];
        });
    }

    /**
     * Create data with complex nested structure
     */
    public function complex(): static
    {
        return $this->state(function (array $attributes) {
            $data = [
                'user_id' => fake()->unique()->numberBetween(1, 10000),
                'profile' => [
                    'personal' => [
                        'first_name' => fake()->firstName(),
                        'last_name' => fake()->lastName(),
                        'birth_date' => fake()->date(),
                        'gender' => fake()->randomElement(['M', 'F', 'O']),
                    ],
                    'contact' => [
                        'email' => fake()->email(),
                        'phone' => fake()->phoneNumber(),
                        'address' => [
                            'street' => fake()->streetAddress(),
                            'city' => fake()->city(),
                            'postal_code' => fake()->postcode(),
                            'country' => fake()->countryCode(),
                        ],
                    ],
                    'preferences' => [
                        'language' => fake()->languageCode(),
                        'timezone' => fake()->timezone(),
                        'notifications' => fake()->boolean(),
                    ],
                ],
                'metadata' => [
                    'created_at' => fake()->iso8601(),
                    'last_login' => fake()->optional()->iso8601(),
                    'source' => fake()->randomElement(['web', 'mobile', 'api']),
                    'tags' => fake()->words(3),
                ],
            ];

            return [
                'data' => $data,
                'row_hash' => md5(json_encode($data)),
            ];
        });
    }
}
