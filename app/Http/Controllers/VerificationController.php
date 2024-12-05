<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Verification;
use phpDocumentor\Reflection\Types\Boolean;
use Google\ApiCore\PagedListResponse;
use Google\Cloud\DocumentAI\V1\Client\DocumentProcessorServiceClient;
use Google\Cloud\DocumentAI\V1\ListProcessorsRequest;
use Google\Cloud\DocumentAI\V1\Processor;
use Google\ApiCore\ApiException;
use Google\Cloud\PrivilegedAccessManager\V1\CheckOnboardingStatusRequest;
use Google\Cloud\PrivilegedAccessManager\V1\CheckOnboardingStatusResponse;
use Google\Cloud\PrivilegedAccessManager\V1\Client\PrivilegedAccessManagerClient;
use Google\Cloud\Iam\Admin\V1\IAMClient;
use Google\Cloud\Iam\V2\Client\PoliciesClient;
use Google\Cloud\Iam\V2\ListPoliciesRequest;
use Google\Cloud\PolicyTroubleshooter\Iam\V3\Client\PolicyTroubleshooterClient;
use Google\Cloud\PolicyTroubleshooter\Iam\V3\TroubleshootIamPolicyRequest;
use Google\Cloud\PolicyTroubleshooter\Iam\V3\TroubleshootIamPolicyResponse;
use Google\ApiCore\OperationResponse;
use Google\Cloud\Iam\V2\CreatePolicyRequest;
use Google\Cloud\Iam\V2\Policy;
use Google\Rpc\Status;
use Illuminate\Support\Facades\Artisan;

class VerificationController extends Controller
{
    public function verify(): bool
    {
        // dd('verify');
        $configuration = Configuration::first();
        // dd($configuration);
        $credentialsPath = storage_path('app/private/' . $configuration->key_path);

    // Check if the credentials file exists
    if (!file_exists($credentialsPath)) {
        $this->insertVerification(false, 'Service account credentials file not found at: ' . $credentialsPath, $configuration);
        return false;
    }

    // Set the environment variable for authentication
    putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");

        $this->insertVerification(true, 'Service account credentials file found at: ' . $credentialsPath, $configuration);
        return true;
    }

    protected function insertVerification($is_success, $reason, $configuration)
    {
        $verification = new Verification();
        $verification->is_success = $is_success;
        $verification->reason = $reason;
        $verification->configuration_id = $configuration->id;
        $verification->save();
    }


    function troubleshoot_iam_policy_sample(): void
{
    // Create a client.
    $policyTroubleshooterClient = new PolicyTroubleshooterClient();

    // Prepare the request message.
    $request = new TroubleshootIamPolicyRequest();

    dd($request);

    // Call the API and handle any network failures.
    try {
        /** @var TroubleshootIamPolicyResponse $response */
        $response = $policyTroubleshooterClient->troubleshootIamPolicy($request);
        dd($response);
        printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
    } catch (ApiException $ex) {
        dd($ex);
        printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
    }
}
    public function listProcessors(){

        try{
            // setup env for credentials
            $configuration = Configuration::first();
            $credentialsPath = storage_path('app/private/' . $configuration->key_path);
            putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");
            $json = json_decode(file_get_contents($credentialsPath), true);

            $projectId = $json['project_id'];
            $serviceAccountEmail = $json['client_email'];

            $gcloudController = new GcloudController();
            $output = $gcloudController->listRolesServiceAccount($serviceAccountEmail, $projectId);
            $roles = json_decode($output, true);
            $requiredRoles = ['roles/documentai.apiUser'];
            $arrayRoles = array_map(function($role) {
                return $role['bindings']['role'];
            }, $roles);
            // save into json 
            dd($arrayRoles);
        }catch(\Exception $e){
            dd($e);
        }
        $configuration = Configuration::first();
        $credentialsPath = storage_path('app/private/' . $configuration->key_path);
        putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");
        
       
        
        $json = json_decode(file_get_contents($credentialsPath), true);
        $projectId = $json['project_id'];
        $this->create_policy_sample('policies/cloudresourcemanager.googleapis.com%2Fprojects%2F' . $projectId . '/denypolicies/my-deny-policy');
        $location = 'us';

        //$this->troubleshoot_iam_policy_sample();
        // $this->listPolicies('policies/cloudresourcemanager.googleapis.com%2Fprojects%2F' . $projectId . '/denypolicies');

        $this->check_onboarding_status_sample('projects/' . $projectId . '/locations/' . $location);
        $client = new DocumentProcessorServiceClient();
       
        $formattedParent = 'projects/' . $projectId . '/locations/' . $location;
        // dd($formattedParent);
        $request = (new ListProcessorsRequest())
        ->setParent($formattedParent);
        

        try {
            /** @var PagedListResponse $response */
            $response = $client->listProcessors($request);
    
            /** @var Processor $element */
            foreach ($response as $element) {
                dd($element);
                printf('Element data: %s' . PHP_EOL, $element->serializeToJsonString());
            }
        } catch (ApiException $ex) {
            dd($ex);
            printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
        }
    }


/**
 * Retrieves the policies of the specified kind that are attached to a
 * resource.
 *
 * The response lists only policy metadata. In particular, policy rules are
 * omitted.
 *
 * @param string $parent The resource that the policy is attached to, along with the kind of policy
 *                       to list. Format:
 *                       `policies/{attachment_point}/denypolicies`
 *
 *
 *                       The attachment point is identified by its URL-encoded full resource name,
 *                       which means that the forward-slash character, `/`, must be written as
 *                       `%2F`. For example,
 *                       `policies/cloudresourcemanager.googleapis.com%2Fprojects%2Fmy-project/denypolicies`.
 *
 *                       For organizations and folders, use the numeric ID in the full resource
 *                       name. For projects, you can use the alphanumeric or the numeric ID.
 */
    protected function listPolicies(string $parent){
// Create a client.
$policiesClient = new PoliciesClient();

// Prepare the request message.
$request = (new ListPoliciesRequest())
    ->setParent($parent);

// Call the API and handle any network failures.
try {
    /** @var PagedListResponse $response */
    $response = $policiesClient->listPolicies($request);
    dd($response);

    /** @var Policy $element */
    foreach ($response as $element) {
        printf('Element data: %s' . PHP_EOL, $element->serializeToJsonString());
    }
} catch (ApiException $ex) {
    dd($ex);
    printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
}
    }

    protected function check_onboarding_status_sample(string $formattedParent){
// Create a client.
$privilegedAccessManagerClient = new PrivilegedAccessManagerClient();

// Prepare the request message.
$request = (new CheckOnboardingStatusRequest())
    ->setParent($formattedParent);

// Call the API and handle any network failures.
try {
    /** @var CheckOnboardingStatusResponse $response */
    $response = $privilegedAccessManagerClient->checkOnboardingStatus($request);
    dd($response);
    printf('Response data: %s' . PHP_EOL, $response->serializeToJsonString());
} catch (ApiException $ex) {
    dd($ex);
    printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
}
    }

    // create policy
    function create_policy_sample(string $parent): void
    {
        // Create a client.
        $policiesClient = new PoliciesClient();
    
        // Prepare the request message.
        $policy = new Policy();
        $policy->setDisplayName('test');
        
        $request = (new CreatePolicyRequest())
            ->setParent($parent)
            ->setPolicy($policy);
    
        // Call the API and handle any network failures.
        try {
            /** @var OperationResponse $response */
            $response = $policiesClient->createPolicy($request);
            $response->pollUntilComplete();
            dd($response);
    
            if ($response->operationSucceeded()) {
                /** @var Policy $result */
                $result = $response->getResult();
                printf('Operation successful with response data: %s' . PHP_EOL, $result->serializeToJsonString());
            } else {
                /** @var Status $error */
                $error = $response->getError();
                printf('Operation failed with error data: %s' . PHP_EOL, $error->serializeToJsonString());
            }
        } catch (ApiException $ex) {
            dd($ex);
            printf('Call failed with message: %s' . PHP_EOL, $ex->getMessage());
        }
    }
    
}
