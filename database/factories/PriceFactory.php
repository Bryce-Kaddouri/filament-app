<?php

namespace Database\Factories;

use App\Models\Price;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Price>
 */
class PriceFactory extends Factory
{
    protected $model = Price::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'provider_id' => Provider::factory(),
            'price' => $this->faker->randomFloat(2, 10, 100), // Random price between 10.00 and 50.00
            'effective_date' => $this->faker->date(),
        ];
    }
}
