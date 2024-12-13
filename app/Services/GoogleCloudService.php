<?php

namespace App\Services;

use Google\Client;
use Google\Service\CloudResourceManager;

class GoogleCloudService
{
    protected $client;

    public function __construct($accessToken)
    {
        $this->client = new Client();
        $this->client->setAccessToken($accessToken);
    }

    public function listOrganizations()
    {
         $service = new CloudResourceManager($this->client);
        $organizations = $service->organizations->get([
            'location' => 'global',
        ]);
        dd($organizations, 'organizations');
        return $organizations->getOrganizations(); 
    }

    public function listProjects($organizationId)
    {

        $service = new CloudResourceManager($this->client);
        
        
        $projects = $service->projects->listProjects(
            [
                'parent' => 'organizations/' . $organizationId,
            ]
        );
        dd($projects, 'projects');
        return $projects->getProjects();
    }
}