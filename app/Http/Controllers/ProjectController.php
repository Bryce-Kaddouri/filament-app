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

    // query builder
    


}
