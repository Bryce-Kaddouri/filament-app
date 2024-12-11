<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Verification;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GcloudController extends Controller
{
    protected $projectId;
    protected array $roles = ['roles/documentai.apiuser', 'roles/documentai.admin'];
    public function __construct()
    {
       $configuration = Configuration::first();
       $filePath = storage_path('app/private/' . $configuration->key_path);
       putenv("GOOGLE_APPLICATION_CREDENTIALS={$filePath}");

       $this->projectId = $configuration->project_id;

    }
    public function runGcloudCommand()
    {
        $process = new Process(['gcloud', 'iam', 'roles', 'describe', 'roles/iam.serviceAccountUser']);
        $process->run();

        // Check if the process failed
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        // Pass the output to the view
        return $process->getOutput();
    }

    public function listSerivceAccounts()
    {
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'list']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    // list roles of the service account
    

    public function createServiceAccount(string $name, string $projectId)
    {
        // parse name to this format Test Create 3 --> test-create-3

        // create txt file in the log folder
        $logPath = storage_path('app/private/log/1.txt');
        $logFile = fopen($logPath, 'w');
        fwrite($logFile, 'Creating service account ' . $name . ' in project ' . $projectId);
        fclose($logFile);
       
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'create', $name, '--project=' . $projectId]);
        $process->run();
        $output = $process->getOutput();
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $output);
        fclose($logFile);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $serviceAccountEmail = $name . '@' . $projectId . '.iam.gserviceaccount.com';
        $this->createKeyServiceAccount($serviceAccountEmail, $projectId);

        return $name;
    }

    public function addRoleServiceAccount(string $serviceAccountEmail, string $projectId, string $role, )
    {
        $process = new Process(['gcloud', 'projects', 'add-iam-policy-binding', $projectId, '--member=serviceAccount:' . $serviceAccountEmail, '--role=' . $role, '--condition=None']);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

   /*  public function getServiceAccount(string $serviceAccountEmail, string $projectId)
    {
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'describe', $serviceAccountEmail, '--project=' . $projectId, '--condition=None']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = $process->getOutput();
        return $result;
    } */

    // check if service account has role
    public function listRoleServiceAccount(string $serviceAccountEmail, string $projectId)
    {
        $process = new Process([
            'gcloud', 
            'projects', 
            'get-iam-policy', 
            $projectId, 
            '--flatten=bindings[].members', 
            '--filter=bindings.members:serviceAccount:' . $serviceAccountEmail, 
            '--format=json'
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = $process->getOutput();
        
        $json = json_decode($result, true);
        $roles = [];
        foreach ($json as $role) {
            $roles[] = $role['bindings']['role'];
        }
        
        return $roles;
    }

    public function createKeyServiceAccount(string $serviceAccountEmail, string $projectId)
    {
        $oldKeyPath = storage_path('app/private/google-credential-key/key.json');
        $logPath = storage_path('app/private/log/2.txt');
        $logFile = fopen($logPath, 'w');
        fwrite($logFile, 'Creating key for service account ' . $serviceAccountEmail . ' in project ' . $projectId);
        fclose($logFile);
        $path = storage_path('app/private/google-credential-key/new.json');
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'keys', 'create', $path, '--iam-account=' . $serviceAccountEmail, '--project=' . $projectId]);
        $process->run();
        $output = $process->getOutput();
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $output);
        fclose($logFile);
        if(!$process->isSuccessful()){
            throw new ProcessFailedException($process);
        }
       
            // remove old key in storage/app/private/google-credential-key/google-credential-key/key.json
            // check if file exists
        if (file_exists($oldKeyPath)) {
            unlink($oldKeyPath);
        }
        // move new key to storage/app/private/google-credential-key/google-credential-key/key.json
        $moved = Storage::disk('local')->move('google-credential-key/new.json', 'google-credential-key/key.json');
        
        return $moved;

        
    }

    public function setupAllRolesServiceAccount(string $displayName )
    {
        try{
            $name = $this->createServiceAccount($displayName, $this->projectId);
            $serviceAccountEmail = $name . '@' . $this->projectId . '.iam.gserviceaccount.com';
            $this->addRoleServiceAccount($serviceAccountEmail, $this->projectId, 'roles/documentai.apiuser');
            $this->addRoleServiceAccount($serviceAccountEmail, $this->projectId, 'roles/documentai.admin');
            return true;
        }catch(Exception $e){
            dd($e);
            return false;
        }
        
        
    }

    // function to check id document ai is enable
    public function checkDocumentAiIsEnable()
    {
        $process = new Process(['gcloud', 'services', 'list', '--enabled', '--format=json', '--filter=documentai.googleapis.com']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $json = json_decode($process->getOutput(), true);
        $isEnable = count($json) > 0;
        return $isEnable;
    }

    // function to enable document ai
    public function enableDocumentAi()
    {
        $process = new Process(['gcloud', 'services', 'enable', 'documentai.googleapis.com']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }

    // list document ai
    public function listDocumentAi()
    {
        $process = new Process(['gcloud', 'documentai', 'documents', 'list']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        
        return $process->getOutput();
    }

    protected function checkIfFileServiceAccountExists()
    {
        $configuration = Configuration::find(1);
    $credentialsPath = storage_path('app/private/google-credential-key/key.json');

    // Check if the credentials file exists
        if (!file_exists($credentialsPath)) {
            return false;
        }else{
            return true;
        }
    }

    protected function getServiceAccount()
    {
        $configuration = Configuration::find(1);
        $credentialsPath = storage_path('app/private/google-credential-key/key.json');
        $json = json_decode(file_get_contents($credentialsPath), true);
        return $json;
    }

    public function verifyServiceAccount(){
        $serviceAccountExists = $this->checkIfFileServiceAccountExists();
        if(!$serviceAccountExists){
            $errorMessage = "Service account file not found";
            $verif = Verification::create([
                'is_success' => false,
                'reason' => $errorMessage,
                'configuration_id' => 1
            ]);
            return $verif;
        }
        $documentAiIsEnable = $this->checkDocumentAiIsEnable();
        if(!$documentAiIsEnable){
            $errorMessage = "Document ai is not enable";
            $verif = Verification::create([
                'is_success' => false,
                'reason' => $errorMessage,
                'configuration_id' => 1
            ]);
            return $verif;
        }
        $serviceAccount = $this->getServiceAccount();
        $serviceAccountEmail = $serviceAccount['client_email'];
        $projectId = $serviceAccount['project_id'];
        $accountHasRole = $this->listRoleServiceAccount($serviceAccountEmail, $projectId);
        $requiredRoles = $this->roles;
        $missingRoles = array_diff($requiredRoles, $accountHasRole);
        if(count($missingRoles) > 0){
            $errorMessage = "Service account does not have role " . implode(', ', $missingRoles);
            $verif = Verification::create([
                'is_success' => false,
                'reason' => $errorMessage,
                'configuration_id' => 1
            ]);
            return $verif;
        }
        $verification = Verification::create([
            'is_success' => true,
            'reason' => "Service account is ready",
            'configuration_id' => 1
        ]);
        return $verification;
        

    }
}

