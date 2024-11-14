<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use ZeeshanTariq\FilamentAttachmate\Core\HandleAttachments;

class EditBill extends EditRecord
{
    use HandleAttachments;
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
