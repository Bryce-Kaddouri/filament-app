<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Price;
use App\Models\Product;
use App\Models\Provider;
use Carbon\Carbon;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all products and providers
        $products = Product::all();
        $providers = Provider::all();

        // Check if there are products and providers
        if ($products->isEmpty() || $providers->isEmpty()) {
            return; // Exit if there are no products or providers
        }

        // Create prices for each product and provider
        foreach ($products as $product) {
            foreach ($providers as $provider) {
                // add 1 prices per month
                $year = Carbon::now()->year;
                for($i = 0; $i < 12; $i++) {
                Price::create([
                    'product_id' => $product->id,
                    'provider_id' => $provider->id,
                    // random price between 10.00 and 500.00
                    'price' => rand(1000, 5000) / 100,
                    'effective_date' => Carbon::create($year, $i + 1, 8)->toDateString(), // Current date
                    ]);
                }
            }
        }
    }
}
