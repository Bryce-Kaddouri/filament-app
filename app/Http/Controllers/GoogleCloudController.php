<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleCloudService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Google\Client;

class GoogleCloudController extends Controller
{
    public function listProjects()
    {
        $user = Auth::user();
        // dd($user);
        if (!$user->access_token) {
            return redirect()->route('google.redirect');
        }

        $client = new Client();
$client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));


        $newAccessToken = $client->getAccessToken();
        dd($newAccessToken, 'newAccessToken');

        $googleCloudService = new GoogleCloudService($user->access_token);

        try {
            // dd($user->access_token, 'access_token');
            // http req to get organizations : https://cloudresourcemanager.googleapis.com/v1beta1/organizations
            $organisations = Http::withHeaders([
                'Authorization' => 'Bearer ' . $user->access_token,
            ])->get('https://cloudresourcemanager.googleapis.com/v1beta1/organizations');

            

            dd($organisations->json(), 'organisations');
            $projects = $googleCloudService->listProjects();
            return $projects;

            /* return view('google.projects', compact('projects')); */
        } catch (\Exception $e) {
            dd($e);
            return redirect()->route('google.redirect')->withErrors('Failed to fetch projects. Please reconnect.');
        }
    }
}
