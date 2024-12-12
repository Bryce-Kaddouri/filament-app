<?php

namespace App\Models;

use App\Http\Controllers\ProjectController;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;
class ServiceAccount extends Model
{
    use Sushi;
    

   

    protected $schema = [
        'description' => 'string',
        'disabled' => 'boolean',
        'displayName' => 'string',
        'email' => 'string',
        'etag' => 'string',
        'name' => 'string',
        'oauth2ClientId' => 'string',
        'projectId' => 'string',
        'uniqueId' => 'string',
    ];

    
}
