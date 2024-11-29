<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Services\ParsedImage;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
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
        dd($data);
        $record->update($data);
        return $record;
    }

    public function mount($record): void
    {
        $this->previousUrl = url()->previous();
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

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
                    'data_for_img' => $dataForFrontend
                ]
            );
        
    }
}
