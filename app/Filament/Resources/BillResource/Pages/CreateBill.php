<?php

namespace App\Filament\Resources\BillResource\Pages;
use Illuminate\Database\Eloquent\Model;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Spatie\PdfToImage\Enums\OutputFormat;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateBill extends CreateRecord
{

    protected static string $resource = BillResource::class;

    protected function getCreatedNotification(): ?Notification
{
    return Notification::make()
        ->success()
        ->title('Bill created')
        ->body('The bill has been created successfully.');
}

protected function handleRecordCreation(array $data): Model
{
    // dd($data);
    if($data['file_type'] === 'image'){
    $fileUrl = storage_path('app/private/livewire-tmp/' . $data['file_url'][0]);
    // check if
    // move file to public folder
    $newPath = storage_path('app/public/bills/' . basename($fileUrl));
    rename($fileUrl, $newPath);
    $data['file_url'] = 'bills/' . basename($fileUrl);
    $data['file_type'] = 'pdf';
    }

   
    /* if($isExist){
        rename($fileUrl, $newPath);
        $newUrl = url('bills/' . basename($fileUrl));
        $data['file_url'] = $newUrl;
    } */
    return static::getModel()::create($data);
}
// override create

}
