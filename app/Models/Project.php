<?php

namespace App\Models;

use App\Http\Controllers\ProjectController;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;
use Sushi\Sushi;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;


class Project extends Model
{

    use Sushi;

    protected $schema = [
        'createTime' => 'string',
        'firebase' => 'boolean',
        'name' => 'string',
        'projectId' => 'string',
        'projectNumber' => 'string',
    ];

    public function getRows()
    {
        // Fetch data from your external source
        $projectController = new ProjectController();
        $projects = $projectController->listProjects();
        // dd($projects);

        return $projects;
    }

    /**
     * Retrieve the list of enabled services for the project.
     *
     * @return array
     */
    public function getEnabledServices()
    {
        // Replace with the actual command to fetch enabled services
        $process = new Process(['gcloud', 'services', 'list', '--enabled', '--format=json', '--project', $this->projectId]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $services = json_decode($process->getOutput(), true);
        //dd($services);
        return array_map(function ($service) {
            return [
                'name' => $service['config']['name'],
                'title' => $service['config']['title'] ?? null,
            ];
        }, $services);
    }

   

    
}

