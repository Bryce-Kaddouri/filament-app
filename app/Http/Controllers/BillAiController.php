<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\Request;
use Google\Cloud\Core\ServiceBuilder;
use GPBMetadata\Google\Appengine\V1\Application;
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\ProcessRequest;
use Google\Cloud\DocumentAI\V1\RawDocument;
use Google\ApiCore\ApiException;
use Google\ApiCore\CredentialsWrapper;
use Google\ApiCore\ClientOptions;
use Google\Cloud\DocumentAI\V1\Document;
use Google\Cloud\DocumentAI\V1\Document\Page;
use Google\Cloud\DocumentAI\V1\Document\TextAnchor\TextSegment;
class BillAiController extends Controller
{

    public function processDocument(string $filePath): Document
{
    // Set the path to your service account key
    $credentialsPath = base_path('test-extract-text-ia-c0d4188a9958.json');

    // Check if the credentials file exists
    if (!file_exists($credentialsPath)) {
        throw new \RuntimeException('Service account credentials file not found at: ' . $credentialsPath);
    }

    // Set the environment variable for authentication
    putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");

    try {
        // Initialize the DocumentProcessorServiceClient with the EU endpoint
        $clientOptions = [
            'credentials' => $credentialsPath,
            'apiEndpoint' => 'eu-documentai.googleapis.com',
        ];
        $client = new DocumentProcessorServiceClient($clientOptions);

        // Read the content of the file to be processed
        $fileContent = file_get_contents($filePath);

        // Create a RawDocument object
        $rawDocument = new RawDocument([
            'content' => $fileContent,
            'mime_type' => 'application/pdf', // Adjust mime type as needed
        ]);

        // Construct the resource name
        $name = sprintf(
            "projects/%s/locations/%s/processors/%s",
            '41599727341',
            'eu', // Ensure this matches your processor's location
            'fad0a438d34786e1' // Replace with your actual processor ID
        );

        // dd($name);

        // Create the ProcessRequest
        $request = new ProcessRequest([
            'name' => $name,
            'raw_document' => $rawDocument,
        ]);

        // Process the document
        $response = $client->processDocument($request);
        $invoice_id = null;
        $invoice_date = null;
        $lines_items_with_qty = [];
        $images_base64 = [];
        $dimensions = null;
        $document  = $response->getDocument();
        foreach($document->getPages() as $page){
            $images_base64[] = $page->getImage()->getContent();
            $dimensions[] = $page->getDimension();
        }

        /* dd($images_base64, $dimensions);
        $entities = $document->getEntities();
        foreach($entities as $entity){
            switch($entity->getType()){
                case 'invoice_id':
                    $invoice_id = $entity->getMentionText();
                    break;
                case 'invoice_date':
                    $invoice_date = $entity->getMentionText();
                    break;
                case 'line_item':
                    
                    if ($entity->getProperties()->count() > 0 ) {
                        // get all
                        $isLineItem = false;
                        foreach($entity->getProperties() as $property){
                            if($property->getType() == 'line_item/unit_price' || $property->getType() == 'line_item/quantity'){
                                $isLineItem = true;
                                break;
                            }
                            
                        }
                        if($isLineItem){
                            $lines_items_with_qty[] = $entity->getProperties();
                        }
                    }
                    break;
            }
        } */
        
        

       return $document;
    } catch (\Exception $e) {
        // Handle exceptions

        dd($e, "rerorw");
        throw new \RuntimeException('Document processing failed: ' . $e->getMessage());
    }
}


// app/Http/Controllers/BillAiController.php
public function showInvoice(Document $document)
{

    dd($document);
    $dataView = [];

    // Extract the base64 image of the first page (if available)
    if ($document->getPages()->count() > 0) {
        $page = $document->getPages()[0];
        $dataView['imageBase64'] = $page->getImage()->getContent();
    }

    // Extract tables
    $tables = [];
    foreach ($document->getPages() as $page) {
        foreach ($page->getTables() as $table) {
            $rows = [];
            foreach ($table->getBodyRows() as $row) {
                $cells = [];
                foreach ($row->getCells() as $cell) {
                    $cells[] = $cell->getLayout()->getTextAnchor()->getTextSegments()[0]->getContent();
                }
                $rows[] = $cells;
            }
            $tables[] = $rows;
        }
    }

    $dataView['tables'] = $tables;

    // Extract fields (Key-Value pairs)
    $fields = [];
    foreach ($document->getEntities() as $entity) {
        $fields[$entity->getType()] = $entity->getMentionText();
    }

    $dataView['fields'] = $fields;

    dd($dataView);

    return $dataView;
}


    public function index(Request $request)
    {

       


        $bill = Bill::find($request->bill);
        $filePath = storage_path('app/public/bills/43/traiteur-modele-facture-wuro.pdf');
    
     
    

        $document = $this->processDocument($filePath);
        $invoiceData = [];

   

    // Pass the extracted data to the Blade view
    return view('invoice', ['document' => $document]);
        
       
    }
}