<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use ZeeshanTariq\FilamentAttachmate\Core\HandleAttachments;

class CreateBill extends CreateRecord
{
    use HandleAttachments;

    protected static string $resource = BillResource::class;

    protected function getCreatedNotification(): ?Notification
{
    return Notification::make()
        ->success()
        ->title('Bill created')
        ->body('The bill has been created successfully.');
}

}
