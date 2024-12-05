<?php

namespace App\Http\Controllers;
use Google\Cloud\Monitoring\V3\Client\MetricServiceClient;
use Google\Cloud\Monitoring\V3\ListTimeSeriesRequest;
use Google\Cloud\Monitoring\V3\ListTimeSeriesRequest\TimeSeriesView;
use Google\Cloud\Monitoring\V3\TimeInterval;
use Google\Cloud\Monitoring\V3\TimeSeries;
use Illuminate\Http\Request;
use Google\ApiCore\ApiException;
use Google\ApiCore\Page;
use Google\Cloud\Billing\V1\Client\CloudBillingClient;
use Google\Cloud\Billing\V1\GetProjectBillingInfoRequest;
use Google\Cloud\Billing\V1\ProjectBillingInfo;
use Google\Cloud\Billing\V1\GetBillingAccountRequest;
use Google\Cloud\Billing\V1\BillingAccount;
use Google\Cloud\Billing\V1\CreateBillingAccountRequest;
use Google\Cloud\Billing\V1\UpdateBillingAccountRequest;
use Google\Cloud\Billing\V1\ListProjectBillingInfoRequest;
use Google\Cloud\Billing\V1\ListBillingAccountsRequest;
use Google\Cloud\Billing\V1\Client\CloudCatalogClient;
use Google\Cloud\Billing\V1\ListServicesRequest;
use Google\Cloud\Billing\V1\Service;
use Google\ApiCore\PagedListResponse;
use Google\Cloud\AppEngine\V1\GetServiceRequest;
use Google\Cloud\Billing\V1\ListSkusRequest;
use Google\Cloud\Billing\V1\Sku;
use Google\Cloud\ServiceUsage\V1\Client\ServiceUsageClient;
use Google\Cloud\ServiceUsage\V1\ListServicesRequest as ServiceUsageListServicesRequest;
use Google\Cloud\ServiceUsage\V1\Service as ServiceUsageService;
use Google\Cloud\ServiceUsage\V1\GetServiceRequest as ServiceUsageGetServiceRequest;
use Google\Cloud\Monitoring\V3\CreateMetricDescriptorRequest;

use Google\Api\MetricDescriptor;
use Google\Protobuf\Timestamp;
use Google\Cloud\Monitoring\V3\Aggregation;
class GoogleBillController extends Controller
{
    public function listBillAccount(){
         // Set the path to your service account key
    $credentialsPath = storage_path('app/private/google-credential-key/key.json');

    // Check if the credentials file exists
    if (!file_exists($credentialsPath)) {
        throw new \RuntimeException('Service account credentials file not found at: ' . $credentialsPath);
    }

    // Set the environment variable for authentication
    putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");

    // dd(getenv("GOOGLE_APPLICATION_CREDENTIALS"));

    // Create a client.

    $cloudBillingClient = new CloudBillingClient();

    $this->getUsage()    ;

    /* $serviceUsageClient = new ServiceUsageClient();

    // Prepare the request message.
    $request = new ServiceUsageListServicesRequest();
    $request->setParent('projects/test-extract-text-ia');
    $response = $serviceUsageClient->listServices($request);

    
    dd($response); */

     // Create a client.
     $serviceUsageClient = new ServiceUsageClient();

     // Prepare the request message.
     $request = new ServiceUsageGetServiceRequest();
     $request->setName('projects/test-extract-text-ia/services/documentai.googleapis.com');
 
     // Call the API and handle any network failures.
     try {
         /** @var Service $response */
         $response = $serviceUsageClient->getService($request);
         dd($response);
         printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
     } catch (ApiException $ex) {
         printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
     }
   


    try{
       
        $services = $this->listServices();
        $documentAi = null;
        foreach($services->iteratePages() as $page){
            $services = $page->getResponseObject()->getServices();

            foreach($services as $service){
                if($service->getDisplayName() == 'Cloud Document AI API'){
                    $documentAi = $service;
                   
                }
            }
        }
        // dd($documentAi);
        $skus = $this->listSkus($documentAi->getName());
        // $totalPrice = $this->calculateTotalPrice($skus);

        
        
     /** @var Page $listAccount */
    $listAccount = $this->listBillingAccount($cloudBillingClient);
    /** @var BillingAccount $billAccount */
    $billAccount = $listAccount->getResponseObject()->getBillingAccounts()[0];
   

    dd($billAccount);
    }catch(ApiException $ex){
        dd($ex);
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }

    
    }


    protected function getUsage(): void {
        $projectId = 'test-extract-text-ia';
        $metricType = 'documentai.googleapis.com/invoice/paid/amount';
        $service = 'documentai.googleapis.com';

        $client = new MetricServiceClient();
        $projectName = $client->projectName($projectId);

        // Check if the metric exists, if not create it
        if (!$this->metricExists($metricType)) {
            $this->createMetricDescriptor($projectName);
        }

        // Define the time interval for the past 7 days
        $now = new Timestamp();
        $now->setSeconds(time());
        $start = new Timestamp();
        $start->setSeconds(time() - 7 * 24 * 60 * 60);
        
        // Create the time interval
        $interval = (new TimeInterval())
            ->setStartTime($start)
            ->setEndTime($now);

        // Create the request
        $request = (new ListTimeSeriesRequest())
            ->setName($projectName)
            ->setFilter("metric.type=\"$metricType\" AND resource.labels.service=\"$service\"")
            ->setInterval($interval)
            ->setView(ListTimeSeriesRequest\TimeSeriesView::FULL);

        // Execute the request
        $response = $client->listTimeSeries($request);

        dd($response);

        // Process the response
        foreach ($response->iterateAllElements() as $timeSeries) {
            // Handle each time series data
        }
    }

    private function metricExists(string $metricType): bool {
        // Logic to check if the metric exists
        // This is a placeholder; implement the actual check
        return false; // Assume it does not exist for this example
    }

    private function createMetricDescriptor(string $name): void {
        echo 'createMetricDescriptor';
        $metricServiceClient = new MetricServiceClient();
        $metricDescriptor = new MetricDescriptor();
        $metricDescriptor->setType('documentai.googleapis.com/invoice/paid/amount')->setMetricKind(MetricDescriptor\MetricKind::GAUGE)->setValueType(MetricDescriptor\ValueType::INT64);

        $request = (new CreateMetricDescriptorRequest())
            ->setName($name)
            ->setMetricDescriptor($metricDescriptor);

        try {
            /** @var MetricDescriptor $response */
            $response = $metricServiceClient->createMetricDescriptor($request);
            printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
        } catch (ApiException $ex) {
            dd($ex);
            printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
        }
    }

    protected function calculateTotalPrice(PagedListResponse $skus){
        $totalPrice = 0.0; 
        

        foreach($skus->iteratePages() as $page){
            $skus = $page->getResponseObject()->getSkus();

            /** @var Sku $sku */
            foreach($skus as $sku){
                dd($sku);
                $skuId = $sku->getSkuId();
                $pricingInfo = $sku->getPricingInfo()[0]->getPricingExpression()->getTieredRates();
                $currencyConversionRate = $sku->getPricingInfo()[0]->getCurrencyConversionRate() ?? 1;

                // Get usage count for this SKU
                $usageCount = $usage[$skuId] ?? 0;
                // Calculate cost for this SKU
                $skuCost = 0.0;
                foreach ($pricingInfo as $tier) {
                    $startUsageAmount = $tier->getStartUsageAmount() ?? 0;
                    $unitPrice = $tier->getUnitPrice()->getNanos() / 1e9 + ($tier->getUnitPrice()->getUnits() ?? 0);
                    // dd($startUsageAmount, $unitPrice);
        
                    if ($usageCount > $startUsageAmount) {
                        $tierUsage = $usageCount - $startUsageAmount;
                        $skuCost += $tierUsage * $unitPrice;
                        $usageCount -= $tierUsage;
                    }
                }
                // Apply currency conversion rate
                $skuCost *= $currencyConversionRate;

                // Add SKU cost to total price
                $totalPrice += $skuCost;
               
            }
        }
        return $totalPrice;
        dd($totalPrice);
    }

    protected function listBillingAccount($cloudBillingClient){
        // Prepare the request message.
    $request = new ListBillingAccountsRequest();

    // Call the API and handle any network failures.
    try {
        /** @var PagedListResponse $response */
        $response = $cloudBillingClient->listBillingAccounts($request);

        /** @var BillingAccount $element */
        /* foreach ($response as $element) {
            printf('Element data: %s' . PHP_EOL, $element->serializeToJsonString());
        } */
        
        return $response->getPage();
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
    }

    protected function listProjectBillingInfo($cloudBillingClient, $billAccountName){
        $cloudBillingClient = new CloudBillingClient();

    // Prepare the request message.
    $request = (new ListProjectBillingInfoRequest())
        ->setName($billAccountName);

    // Call the API and handle any network failures.
    try {
        /** @var PagedListResponse $response */
        $response = $cloudBillingClient->listProjectBillingInfo($request);

        /** @var ProjectBillingInfo $element */
        /* foreach ($response as $element) {
            printf('Element data: %s' . PHP_EOL, $element->serializeToJsonString());
        } */
        return $response;
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
    }
    

    protected function getProjectBillingInfoCustom($cloudBillingClient){
// Prepare the request message.
$request = (new GetProjectBillingInfoRequest())
->setName('projects/test-extract-text-ia');

// Call the API and handle any network failures.
try {
/** @var ProjectBillingInfo $response */
$response = $cloudBillingClient->getProjectBillingInfo($request);
return $response;
printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
} catch (ApiException $ex) {
printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
}
    }



    protected function getBillingAccount($cloudBillingClient,$billAccountName){
        $request = (new GetBillingAccountRequest())
        ->setName($billAccountName);

    // Call the API and handle any network failures.
    try {
        /** @var BillingAccount $response */
        $response = $cloudBillingClient->getBillingAccount($request);
        return $response;
        printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
    }

    protected function createBillAccount($cloudBillingClient){
        // Prepare the request message.
    $billingAccount = new BillingAccount();
    $request = (new CreateBillingAccountRequest())
        ->setBillingAccount($billingAccount);

    // Call the API and handle any network failures.
    try {
        /** @var BillingAccount $response */
        $response = $cloudBillingClient->createBillingAccount($request);
        printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
    }


    protected function updateBillAccount($cloudBillingClient,$billAccountName){
        $account = new BillingAccount();
        $request = (new UpdateBillingAccountRequest())
            ->setName($billAccountName)
            ->setAccount($account);
    
        // Call the API and handle any network failures.
        try {
            /** @var BillingAccount $response */
            $response = $cloudBillingClient->updateBillingAccount($request);
            printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
        } catch (ApiException $ex) {
            printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
        }
    }


    protected function listServices(){
        // Create a client.
    $cloudCatalogClient = new CloudCatalogClient();

    // Prepare the request message.
    $request = new ListServicesRequest();

    // Call the API and handle any network failures.
    try {
        /** @var PagedListResponse $response */
        $response = $cloudCatalogClient->listServices($request);

        /** @var Service $element */
        foreach ($response as $element) {
            // printf('Element data: %s' . PHP_EOL, $element->serializeToJsonString());
        }
        return $response;
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
    }

    protected function listSkus($serviceName){
        // Create a client.
    $cloudCatalogClient = new CloudCatalogClient();

    // Prepare the request message.
    $request = (new ListSkusRequest())
        ->setParent($serviceName);

    // Call the API and handle any network failures.
    try {
        /** @var PagedListResponse $response */
        $response = $cloudCatalogClient->listSkus($request)->getPage()->getResponseObject()->serializeToJsonString();
         dd($response);

        /** @var Sku $element */
        foreach ($response as $element) {
            // printf('Element data: %s' . PHP_EOL, $element->serializeToJsonString());
        }
        return $response;
    } catch (ApiException $ex) {
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
    }


    protected function getPrice($serviceName){
        // http request 
    }


}
