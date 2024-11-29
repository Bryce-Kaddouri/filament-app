<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Models\LineItem;
use App\Services\ParsedImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class EditBill extends EditRecord
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    public function handleRecordUpdate(Model $record, array $data): Model
    {
        // dd($data);
        // remove the file_url from the data
        unset($data['file_url']);
        // remove json_document from the data
        unset($data['json_document']);
        $record->update($data);
        LineItem::where('bill_id', $record->id)->delete();
        foreach ($data['all_line_items'] as $lineItem) {
            LineItem::create([
                'bill_id' => $record->id,
                'product_id' => $lineItem['product_id'],
                'quantity' => $lineItem['quantity'],
                'provider_id' => $record->provider_id,
                'unit_price' => $lineItem['unit_price'],
            ]);
        }
        return $record;
    }

    public function mount($record): void
    {
        $this->previousUrl = url()->previous();
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();
        Log::info($this->record);

        $documentJson = Storage::get('bills/' . $this->record->id . '/json_document.json');
        //dd(json_decode($documentJson, true)   );
        $parsedImage = ParsedImage::fromJson($documentJson);
        $dataForFrontend = $parsedImage->toJsonSerializable();
       //  dd($parsedImage);

            $this->form->fill(
                [
                    'provider_id' => $this->record->provider_id,
                    'bill_number' => $this->record->bill_number,
                    'bill_date' => $this->record->bill_date,
                    'file_url' => $this->record->file_url,
                    'json_document' => $documentJson,
                    'line_items' => $parsedImage->getLineItems(),
                    'generated_data' => $dataForFrontend,
                    'data_for_img' => $dataForFrontend,
                    'all_line_items' => $this->record->line_items,
                ]
            );
        
    }
}
