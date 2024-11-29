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
        dd(json_decode($documentJson, true)   );
        $parsedImage = ParsedImage::fromJson($documentJson);
        dd($parsedImage);

        if (! $this->hasInfolist()) {
            $this->fillForm();
            $this->form->fill(
                ['']
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
