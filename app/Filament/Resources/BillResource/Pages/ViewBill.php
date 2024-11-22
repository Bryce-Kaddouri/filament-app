<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewBill extends ViewRecord
{
    protected static string $resource = BillResource::class;

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
