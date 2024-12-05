<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GcloudController extends Controller
{
    protected $projectId;
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
    public function listRolesServiceAccount(string $serviceAccountEmail)
    {

        /* gcloud projects get-iam-policy test-extract-text-ia \
        --flatten="bindings[].members" \
        --format='table(bindings.role)' \
        --filter="bindings.members:bill-account@test-extract-text-ia.iam.gserviceaccount.com" */
        // dd($serviceAccountEmail, $projectId);
        $process = new Process(['gcloud', 'projects', 'get-iam-policy', $this->projectId, '--flatten=bindings[].members', '--format=json', '--filter=bindings.members:' . $serviceAccountEmail]);
        // display command 
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    public function createServiceAccount(string $name, string $projectId)
    {
        // parse name to this format Test Create 3 --> test-create-3
       
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'create', $name, '--project=' . $projectId]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $serviceAccountEmail = $name . '@' . $projectId . '.iam.gserviceaccount.com';
        $this->createKeyServiceAccount($serviceAccountEmail, $projectId);

        return $name;
    }

    public function addRoleServiceAccount(string $serviceAccountEmail, string $projectId, string $role, )
    {
        $process = new Process(['gcloud', 'projects', 'add-iam-policy-binding', $projectId, '--member=serviceAccount:' . $serviceAccountEmail, '--role=' . $role, ]);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    public function getServiceAccount(string $serviceAccountEmail, string $projectId)
    {
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'describe', $serviceAccountEmail, '--project=' . $projectId, '--condition=None']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = $process->getOutput();
        return $result;
    }

    public function createKeyServiceAccount(string $serviceAccountEmail, string $projectId)
    {
        $oldKeyPath = storage_path('app/private/google-credential-key/key.json');
        $path = storage_path('app/private/google-credential-key/new.json');
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'keys', 'create', $path, '--iam-account=' . $serviceAccountEmail, '--project=' . $projectId]);
        $process->run();
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
        $name = $this->createServiceAccount($displayName, $this->projectId);
        $serviceAccountEmail = $name . '@' . $this->projectId . '.iam.gserviceaccount.com';
        $this->addRoleServiceAccount($serviceAccountEmail, $this->projectId, 'roles/documentai.apiuser');
        $this->addRoleServiceAccount($serviceAccountEmail, $this->projectId, 'roles/documentai.admin');
        $serviceAccount = $this->getServiceAccount($serviceAccountEmail, $this->projectId);
        
        return $serviceAccount;
    }
}
