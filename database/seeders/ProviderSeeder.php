<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Provider;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Provider::factory()->count(10)->create([
            'image' => 'https://placehold.co/400x400.png', // Set a default image for each provider
        ]); // Create 10 providers using the factory
    }
}
