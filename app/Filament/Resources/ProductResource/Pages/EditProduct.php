<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Models\ProductCodeByProvider;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    // mount record
    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->record->load('products_code_by_provider');
        // fill form
        $this->form->fill([
            'name' => $this->record->name,
            'description' => $this->record->description,
            'image' => $this->record->image,
            'products_code_by_provider' => $this->record->products_code_by_provider,
        ]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $product_data = array_filter($data, function($key) {
            return $key !== 'selected_providers';
        }, ARRAY_FILTER_USE_KEY);

        $record->update($product_data);
        $products_code_by_provider = [];

        foreach ($data['products_code_by_provider'] as $item) {
            $products_code_by_provider[] = [
                'provider_id' => $item['provider_id'],
                'product_id' => $record->id,
                'code' => $item['code']
            ];
        }

        // Delete existing ProductCodeByProvider records for the product
        ProductCodeByProvider::where('product_id', $record->id)->delete();

        // Create new ProductCodeByProvider records
        foreach ($products_code_by_provider as $data) {
            ProductCodeByProvider::create($data);
        }

        return $record;
    }
}
