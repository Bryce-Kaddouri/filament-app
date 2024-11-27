<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewProduct extends ViewRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    // update record
    public function mount(int | string $record): void
    {
        // dd($record);
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
}
