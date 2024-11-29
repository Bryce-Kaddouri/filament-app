<?php

namespace App\Filament\Resources\BillResource\Pages;
use Illuminate\Database\Eloquent\Model;

use App\Filament\Resources\BillResource;
use App\Http\Controllers\BillAiController;
use App\Models\LineItem;
use App\Models\Provider;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use GuzzleHttp\Psr7\Request as Psr7Request;
use Spatie\PdfToImage\Enums\OutputFormat;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Google\Cloud\DocumentAI\V1\Document\Entity;
use Google\Protobuf\Internal\RepeatedField;
use Carbon\Exceptions\InvalidFormatException;
use Imagick;
use App\Services\ParsedImage;
use Illuminate\Support\Facades\Storage;

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

   
    /* if($isExist){
        rename($fileUrl, $newPath);
        $newUrl = url('bills/' . basename($fileUrl));
        $data['file_url'] = $newUrl;
    } */


   
    $bill = static::getModel()::create([
        'provider_id' => $data['provider_id'],
        'bill_number' => $data['bill_number'],
        'bill_date' => $data['bill_date'],
        'file_url' => $data['file_url'],
        'file_type' => 'pdf',
        'json_document' =>null,
    ]);
    // write json document to file
    $jsonDocument = $data['json_document'];
    
    Storage::put('bills/' . $bill->id . '/json_document.json', $jsonDocument,  'private');
    $url = 'bills/' . $bill->id . '/json_document.json';
    $bill->json_document = $url;
    $bill->save();

    // create line items
    foreach ($data['all_line_items'] as $lineItem) {
        // dd($lineItem);
        LineItem::create([
            'bill_id' => $bill->id,
            'provider_id' => $data['provider_id'],
            'quantity' => (float) $lineItem['quantity'],
            'unit_price' => (float) $lineItem['unit_price'],
            'product_id' => $lineItem['product_id'],
        ]);
    }
    return $bill;


    
    
}

// fill form 

    private function parseDate(string $dateString): ?\Carbon\Carbon
    {
        // Trim the date string to remove any leading or trailing whitespace
        $formats = [
            'Y-m-d',        // 2024-11-12
            'd/m/Y',        // 12/11/2024
            'd M Y',        // 12 Nov 2024
            'd F Y',        // 12 November 2024
            'm/d/Y',        // 11/12/2024
            'Y/m/d',        // 2024/11/12
            // Add more formats as needed
        ];
    
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString);
            } catch (InvalidFormatException $e) {
                // Try the next format
                continue;
            }
        }
    
        // If no formats match, throw an exception or return null
        /* throw new InvalidFormatException("Invalid date format: $dateString"); */

        return null; // Return null if no format matched
    }

// header action 
    

}
