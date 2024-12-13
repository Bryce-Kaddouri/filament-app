<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Project;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Override;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ProjectController extends Controller
{
    protected Configuration $configuration;

    public function __construct()
    {
        $this->configuration = Configuration::first();
    }

    

    public function listProjectsApi(){
        // Socialite::driver('google')->stateless()->redirect();
        $user = Socialite::driver('google')->stateless()->user();
         // dd(vars: $user);

        $accessToken ='';
        dd($accessToken);
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $accessToken);
        $response = Http::get('https://cloudresourcemanager.googleapis.com/v1/projects');
        return $response->json();
    }

    public function listProjects()
    {
        $process = new Process(['gcloud', 'projects', 'list', '--format=json', '--sort-by=name']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        $projects = json_decode($process->getOutput(), true);
        $projectsModel = [];
        // check if labels field exist , if not create it
        foreach ($projects as $project) {
            if (!isset($project['labels'])) {
                $project['firebase'] = false;
                // add to projectsModel
                $projectsModel[] = $project;
            }else{
                $project['firebase'] = true;
                // remove labels field
                unset($project['labels']);
                $projectsModel[] = $project;
            }
            
        }
        // dd($projectsModel);
        return $projectsModel;
    }

    // function to list services accounts of a project
    public function listServicesAccounts(string $projectId)
    {
        $process = new Process(['gcloud', 'iam', 'service-accounts', 'list', '--format=json', '--project=' . $projectId]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }

    // list billing accounts of a project
    public function listBillingAccounts(string $projectId)
    {
        $process = new Process(['gcloud', 'billing', 'accounts', 'list', '--format=json', '--project=' . $projectId]);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        return $process->getOutput();
    }

    // function to create a project
    public function createProject(string $projectId)
    {   
        
        $command = ['gcloud', 'projects', 'create', $projectId];
        
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            $error = new ProcessFailedException($process);
            return json_encode(["status" => "error", "message" => "Project creation failed", "result" => $error->getMessage()]);
        }
        
        return json_encode(["status" => "success", "message" => "Project created successfully", "result" => $process->getOutput()]);
    }

    // create service account for project
    public function createServiceAccount(string $projectId, string $serviceAccountId)
    {
        $command = ['gcloud', 'iam', 'service-accounts', 'create', $serviceAccountId, '--project=' . $projectId];
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            return json_encode(["status" => "error", "message" => "Service account creation failed", "result" => $process->getOutput()]);
        }
        return json_encode(["status" => "success", "message" => "Service account created successfully", "result" => $process->getOutput()]);
    }

    // list processors of a project
    public function listProcessors(string $projectId)
    {
        // use this ap req:
        /* curl -X GET \
  -H "Authorization: Bearer $(gcloud auth application-default print-access-token)" \
  "https://eu-documentai.googleapis.com/v1beta3/projects/test-extract-text-ia/locations/eu/processors" */
        

        $accessToken = '';
        dd($accessToken);
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $accessToken);
        $response = Http::get('https://eu-documentai.googleapis.com/v1beta3/projects/test-extract-text-ia/locations/eu/processors');
        return $response->json();
    }

    // create docuemnt ai invoice parser processor
    public function createDocumentAiInvoiceParserProcessor(string $projectId, string $processorId)
    {
        $command = ['gcloud', 'documentai', 'processors', 'create', $processorId, '--project=' . $projectId];
        $commandLine = implode(' ', $command);
        dd($commandLine);
        $process = new Process($command);
        $process->run();
        if (!$process->isSuccessful()) {
            return json_encode(["status" => "error", "message" => "Processor creation failed", "result" => $process->getOutput()]);
        }
        return json_encode(["status" => "success", "message" => "Processor created successfully", "result" => $process->getOutput()]);
    }
    


}
