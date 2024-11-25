<?php

namespace App\Filament\Resources\BillResource\Pages;
use Illuminate\Database\Eloquent\Model;

use App\Filament\Resources\BillResource;
use App\Http\Controllers\BillAiController;
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

// fill form 

public function mount(): void
    {
        $fileUrl = request()->file_url;
        $provider_id = request()->provider_id;
        $billAiController = new BillAiController();
        $document = $billAiController->processDocument($fileUrl, false);
        $bill_id = null;
        $bill_date = null;
        $supplier_name = null;
        $lines_items_with_qty = [];
        $images_base64 = [];
        $dimensions = null;

        $entities = $document->getEntities();
        // dd($entities);
        foreach($entities as $entity){
            /*
             * @var Entity $entity
             */
            if($entity->getType() === 'supplier_name'){
                $supplier_name = $entity->getMentionText();
            }
            if($entity->getType() === 'invoice_date'){
                $bill_date = $entity->getMentionText();
            }
            if($entity->getType() === 'invoice_id'){
                    $bill_id = $entity->getMentionText();
            }
            if($entity->getType() === 'line_item'){
                $properties = $entity->getProperties();
                $hasQty = false;
                $hasUnitPrice = false;
                foreach($properties as $property){
                    /*
                     * @var Property $property
                     */
                    
                    if($property->getType() === 'line_item/quantity'){
                        $hasQty = true;
                    }
                    if($property->getType() === 'line_item/unit_price'){
                        $hasUnitPrice = true;
                    }
                   
                }
                if($hasQty && $hasUnitPrice){
                    $lines_items_with_qty[] = $properties;
                }

            }
            
            // dd($entity);
            
        }

        // images
        foreach($document->getPages() as $page){
            $images_base64[] = $page->getImage();
            $dimensions[] = $page->getDimension();
        }

        // Normalize the supplier name by removing unwanted characters and extra spaces
        $cleanedSupplierName = preg_replace('/\s+/', ' ', trim($supplier_name)); // Normalize spaces
        $cleanedSupplierName = preg_replace('/[^a-zA-Z0-9 à-ÿ]/u', '', $cleanedSupplierName); // Remove unwanted characters
        // dimensions

        // dd($bill_id, $bill_date, $supplier_name, $lines_items_with_qty, $images_base64, $dimensions);
        $parsedBillDate = $this->parseDate($bill_date);


        
        $imagesTest = array();
        $index = 0;
        foreach($images_base64 as $image){
            $boj = [
                'mime_type' => $image->getMimeType(),
                'content_encoded' => base64_encode($image->getContent()),
                'width' => $image->getWidth(),
                'height' => $image->getHeight(),
                'points' => [],
            ];
            $imagesTest[] = $boj;
            $index++;
        }
        $points = [];
        // add border to text in the images based on entities
        // dd($entities);
        for($j=0; $j < count($entities); $j++){
            if($entities[$j]->getType() === 'supplier_name'){
            $entity = $entities[$j];
                $pageAnchor = $entity->getPageAnchor();
                $pageRef = $pageAnchor->getPageRefs();
                $pageRef = $pageRef[0];
                $page = $pageRef->getPage();
                $boundingPoly = $pageRef->getBoundingPoly();
                // dd($boundingPoly->getNormalizedVertices());
                $vertex = $boundingPoly->getNormalizedVertices();
                foreach($vertex as $v){
                    // add to points
                    $points[] = array(
                        'x' => $v->getX(),
                        'y' => $v->getY(),
                    );
                }
            }

            $imagesTest[0]['supplier_name_points'] = $points;
                   
                

                // dd($pageAnchor);
            

    
        }

         $imagesTest[0]['points'] = $points;

         // Load the image using Imagick
         // modify the image to add the points to the image to add a sqaure on top right corner with bg red


       /*  dd($imagesTest);

        dd($entities);

        dd($imagesTest); */

        // dd($imagesTest);


        
        $this->form->fill([
            'my_images' => $imagesTest,
            'provider_id' => $provider_id,
            'file_type' => 'pdf',
            'bill_number' => $bill_id,
            'file_url' => $fileUrl,
            'bill_date' => $parsedBillDate,
            'line_items' => array_map(function(RepeatedField $item){
                
                $field = array();
                foreach($item as $property){
                    /*
                     * @var Property $property
                     */
                    if($property->getType() === 'line_item/quantity'){
                        $field['qty'] = $property->getMentionText();
                    }
                    if($property->getType() === 'line_item/unit_price'){
                        $field['unit_price'] = $property->getMentionText();
                    }
                    if($property->getType() === 'line_item/product'){
                        $field['product'] = null;
                    }
                }
                /* $field['qty'] = '12';
                $field['unit_price'] = '12';
                $field['product'] = '12'; */
                return $field;
            }, $lines_items_with_qty),
        ]);
    }

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
