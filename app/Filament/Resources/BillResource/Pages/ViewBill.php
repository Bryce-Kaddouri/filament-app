<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Models\Bill;
use App\Services\ParsedImage;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewBill extends ViewRecord
{
    protected static string $resource = BillResource::class;

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $documentJson = Storage::get('bills/' . $this->record->id . '/json_document.json');
        //dd(json_decode($documentJson, true)   );
        $parsedImage = ParsedImage::fromJson($documentJson);
        $dataForFrontend = $parsedImage->toJsonSerializable();
       //  dd($parsedImage);

        if (! $this->hasInfolist()) {
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

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('bill-ai')
                ->url(fn () => route('bill-ai.index', ['bill' => $this->record->id]))
                ->label('AI')
                ->icon('heroicon-o-sparkles'),
        ];
    }


}
