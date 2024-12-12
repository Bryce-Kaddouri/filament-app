<?php

namespace App\Http\Controllers;

use App\Events\LogUpdated;
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
    protected $roleTitle = 'Document AI User For Bill App';
    protected $roleName = 'billAppRole';
    
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
        $this->selectProject($projectId);


        // check if role project already exists
        $roleProjectExists = $this->RoleProjectAlreadyExists($projectId);
        // dd($roleProjectExists);
        if(!$roleProjectExists){
            $this->createRoleProject($projectId);
        }

        

        // create txt file in the log folder
        $logPath = storage_path('app/private/log/1.txt');
        // check if file exists
        if (!file_exists($logPath)) {
            // create file
            Storage::disk('local')->put('log/1.txt', '');
            
        }
        $logFile = fopen($logPath, 'w');
        fwrite($logFile, 'Creating service account ' . $name . ' in project ' . $projectId . PHP_EOL);
        fclose($logFile);


        // write some text in the file
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, 'Creating service account ' . $name . ' in project ' . $projectId  . PHP_EOL);
        fclose($logFile);
       
        $command = ['gcloud', 'iam', 'service-accounts', 'create', $name, '--project=' . $projectId];
         $process = new Process($command);
         $commandLine = implode(' ', $command);
         
         // format to one line 
        
        $process->run();
        
        // dd($output);
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $commandLine . PHP_EOL);

        if (!$process->isSuccessful()) {
            // dd($process->getErrorOutput());

            
            // write error in the file
            $logFile = fopen($logPath, 'a');
            fwrite($logFile, $process->getErrorOutput() . PHP_EOL);
            fclose($logFile);
            return false;
        }
        $output = $process->getOutput();
        fwrite($logFile, $output . PHP_EOL);
        fclose($logFile);
        $serviceAccountEmail = $name . '@' . $projectId . '.iam.gserviceaccount.com';
        $canCreateKey = $this->createKeyServiceAccount($serviceAccountEmail, $projectId);
        if(!$canCreateKey){
            return false;
        }
        $canAddRoles = $this->addRolesServiceAccount($serviceAccountEmail, $projectId, $this->roles);
        if(!$canAddRoles){
            return false;
        }
        

        return true; 
    }

    public function addRolesServiceAccount(string $serviceAccountEmail, string $projectId, array $roles)
    {
        $logPath = storage_path('app/private/log/1.txt');
        foreach($roles as $role){   
            $logFile = fopen($logPath, 'a');
            fwrite($logFile, 'Adding role ' . $role . ' to service account ' . $serviceAccountEmail . ' in project ' . $projectId . PHP_EOL);
            fclose($logFile);
            $command = ['gcloud', 'projects', 'add-iam-policy-binding', $projectId, '--member=serviceAccount:' . $serviceAccountEmail, '--role=' . $role];
            $process = new Process($command);
            $commandLine = implode(' ', $command);
            $logFile = fopen($logPath, 'a');
            fwrite($logFile, $commandLine . PHP_EOL);
            fclose($logFile);
            $process->run();
        

            if (!$process->isSuccessful()) {
                // Log the specific error message
                $logFile = fopen($logPath, 'a');
                fwrite($logFile, 'Error adding role: ' . $process->getErrorOutput() . PHP_EOL);
                fclose($logFile);
                return false;
            }
            $logFile = fopen($logPath, 'a');
            fwrite($logFile, $process->getOutput() . PHP_EOL);
            fclose($logFile);

            
        }
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, 'All roles added successfully' . PHP_EOL);
        fclose($logFile);
        return true;
    }

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

    protected function listProjects()
    {
        $process = new Process(['gcloud', 'projects', 'list']);
        $process->run();
        return $process->getOutput();
    }

    protected function selectProject(string $projectId)
    {   $logPath = storage_path('app/private/log/1.txt');
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, 'Selecting project ' . $projectId . PHP_EOL);
        fclose($logFile);
        $command = ['gcloud', 'config', 'set', 'project', $projectId];
        $commandLine = implode(' ', $command);
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $commandLine . PHP_EOL);
        fclose($logFile);
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            $logFile = fopen($logPath, 'a');
            fwrite($logFile, $process->getErrorOutput() . PHP_EOL);
            fclose($logFile);
            return false;
        }
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $process->getOutput() . PHP_EOL);
        fclose($logFile);
        return true;
    }

    public function createKeyServiceAccount(string $serviceAccountEmail, string $projectId)
    {
        
        $oldKeyPath = storage_path('app/private/google-credential-key/key.json');
        $logPath = storage_path('app/private/log/1.txt');
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, 'Creating key for service account ' . $serviceAccountEmail . ' in project ' . $projectId . PHP_EOL);
        fclose($logFile);
        $path = storage_path('app/private/google-credential-key/new.json');

        $command = ['gcloud', 'iam', 'service-accounts', 'keys', 'create', $path, '--iam-account=' . $serviceAccountEmail, '--project=' . $projectId];
        $process = new Process($command);
        $commandLine = implode(' ', $command);
        $process->run();
        $output = $process->getOutput();
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $commandLine . PHP_EOL);
        fwrite($logFile, $output . PHP_EOL);
        fclose($logFile);
        if(!$process->isSuccessful()){
            /* $error =  new ProcessFailedException($process); */
            // dd($process->getErrorOutput(), 'error from create key');
            $logFile = fopen($logPath, 'a');
            fwrite($logFile, $process->getErrorOutput() . PHP_EOL);
            fclose($logFile);
            return false;
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


    protected function listRolesProject(string $projectId)
    {
        $logPath = storage_path('app/private/log/1.txt');
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, 'Listing roles in project ' . $projectId . PHP_EOL);
        fclose($logFile);
        $command = ['gcloud', 'iam', 'roles', 'list', '--project=' . $projectId, '--format=json'];
        $process = new Process($command);
        $commandLine = implode(' ', $command);
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $commandLine . PHP_EOL);
        fclose($logFile);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $process->getOutput() . PHP_EOL);
        fclose($logFile);
        return $process->getOutput();
    }

    protected function roleProjectAlreadyExists(string $projectId)
    {
        $json = $this->listRolesProject($projectId);
        //dd($json);
        $json = json_decode($json, true);
        foreach($json as $role){
            if($role['name'] == 'projects/'.$projectId.'/roles/'.$this->roleName){
                return true;
            }
        }
        return false;
    }

    protected function createRoleProject(string $projectId)
    {
        $logPath = storage_path('app/private/log/1.txt');
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, 'Creating role project ' . $this->roleTitle . ' in project ' . $projectId . PHP_EOL);
        fclose($logFile);
        $permissions = [
            'documentai.locations.get',
            'documentai.locations.list', 
            'documentai.operations.getLegacy', 
            'documentai.processorTypes.get', 
            'documentai.processorTypes.list',
            'documentai.processors.create',
            'documentai.processors.delete',
            'documentai.processors.get',
            'documentai.processors.list',
            'documentai.processors.processBatch',
            'documentai.processors.processOnline',
            'documentai.processors.update',
            'documentai.processorVersions.create',
            'documentai.processorVersions.delete',
            'documentai.processorVersions.get',
            'documentai.processorVersions.list',
            'documentai.processorVersions.processBatch',
            'documentai.processorVersions.processOnline',
            'documentai.processorVersions.update',
            'documentai.evaluations.create',
            'documentai.evaluations.get',
            'documentai.evaluations.list'
        ];
        $command = [
            'gcloud', 
            'iam', 
            'roles', 
            'create', 
            $this->roleName, 
            '--project=' . $projectId, 
            '--title="' . $this->roleTitle . '"', 
            '--description="Role for Bill App"', 
            '--permissions="' . implode(',', $permissions) . '"', 
            '--stage="GA"'
        ];
        $process = new Process($command);
        $commandLine = implode(' ', $command);
        // dd($commandLine);
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $commandLine . PHP_EOL);
        fclose($logFile);
        $process->run();
        if (!$process->isSuccessful()) {
            $logFile = fopen($logPath, 'a');
            fwrite($logFile, $process->getErrorOutput() . PHP_EOL);
            fclose($logFile);
            return false;
        }
        $logFile = fopen($logPath, 'a');
        fwrite($logFile, $process->getOutput() . PHP_EOL);
        fclose($logFile);
        return true;
    }

    
}
