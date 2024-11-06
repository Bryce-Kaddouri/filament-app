<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Provider;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 providers and recycle them
        $providers = Provider::factory()->count(10)->create();

        // Create 10 products and recycle the providers
        Product::factory()
            ->count(10)
            ->recycle($providers) // Recycle the providers
            ->create([
                'image' => 'https://placehold.co/400x400.png', // Set a default image for each product
            ])
            ->each(function ($product) use ($providers) {
                // Attach random providers to each product
                $randomProviders = $providers->random(rand(1, 3))->pluck('id');
                $product->providers()->attach($randomProviders);
            });
    }
}
