<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\ApiCore\ApiException;
use Google\Cloud\Billing\V1\Client\CloudBillingClient;
use Google\Cloud\Billing\V1\GetProjectBillingInfoRequest;
use Google\Cloud\Billing\V1\ProjectBillingInfo;
use Google\Cloud\Billing\V1\GetBillingAccountRequest;
class GoogleBillController extends Controller
{
    public function listBillAccount(){
         // Set the path to your service account key
    $credentialsPath = base_path('google_credential.json');

    // Check if the credentials file exists
    if (!file_exists($credentialsPath)) {
        throw new \RuntimeException('Service account credentials file not found at: ' . $credentialsPath);
    }

    // Set the environment variable for authentication
    putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");

    // Create a client.
    $cloudBillingClient = new CloudBillingClient();

    try{

    $billAccountName = $this->getProjectBillingInfoCustom($cloudBillingClient)->getBillingAccountName();
    dd($this->getBillingAccount($cloudBillingClient,$billAccountName));
    dd($billAccountName);
    }catch(ApiException $ex){
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


}
