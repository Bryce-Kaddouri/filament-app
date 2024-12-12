<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Project;
use Illuminate\Http\Request;
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
    // query builder
    


}
