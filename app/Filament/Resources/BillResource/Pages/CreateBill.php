<?php

namespace App\Filament\Resources\BillResource\Pages;
use Illuminate\Database\Eloquent\Model;

use App\Filament\Resources\BillResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Spatie\PdfToImage\Enums\OutputFormat;

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
   $bill = static::getModel()::create($data);
   
   if($data['file_type'] === 'pdf'){
    /* $directoryToWhereImagesShouldBeStored = storage_path('app/public/');
    $pdf = new \Spatie\PdfToImage\Pdf($directoryToWhereImagesShouldBeStored . $data['file_urls']); */
    
    /** @var int $numberOfPages */
    /* $numberOfPages = $pdf->pageCount();
    $numberOfPages = $pdf->pageCount();
    $images = []; */
    // cehck if bills/bill_id exists
    /* if (!file_exists($directoryToWhereImagesShouldBeStored. 'bills/' . $bill->id)) {
        mkdir($directoryToWhereImagesShouldBeStored. 'bills/' . $bill->id, 0777, true);
    }
    for ($i = 1; $i <= $numberOfPages; $i++) {
        $images[] = $pdf
        ->format(OutputFormat::Png)
        ->selectPage($i)
        ->save($directoryToWhereImagesShouldBeStored. 'bills/' . $bill->id . '/' . $i . '.png')[0];
    } */
   
    // update bill with image urls
    /* $bill->update(['image_urls' => $images]);    */
    // dd($bill);
    // dd($directoryToWhereImagesShouldBeStored, $data, $pdf, $images);
    return $bill;
}else{
    $bill->update(['file_urls' => [$data['file_urls']]]);
    return $bill;
}
}

// override create

}
