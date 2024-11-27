<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductCodeByProvider;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $product_data= array_filter($data, function($key){
            return $key !== 'selected_providers';
        }, ARRAY_FILTER_USE_KEY);

        $product = static::getModel()::create($product_data);
        $products_code_by_provider = array();
       
        $i = 0;
        foreach($data['products_code_by_provider'] as $data){
            $products_code_by_provider[] = [
                'provider_id' => $data['provider_id'],
                'product_id' => $product->id,
                'code' => $data['code']
            ];
            $i++;
        }


        // dd($products_code_by_provider, $product);

        foreach ($products_code_by_provider as $data) {
            $data['product_id'] = $product->id; // Set the product_id
            // Create a new ProductCodeByProvider record
            ProductCodeByProvider::create($data);
        }


        return $product;
    }
}
