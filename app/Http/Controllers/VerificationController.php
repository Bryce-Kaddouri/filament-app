<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\Verification;
use phpDocumentor\Reflection\Types\Boolean;

class VerificationController extends Controller
{
    public function verify(): bool
    {
        // dd('verify');
        $configuration = Configuration::first();
        // dd($configuration);
        $credentialsPath = storage_path('app/private/' . $configuration->key_path);

    // Check if the credentials file exists
    if (!file_exists($credentialsPath)) {
        $this->insertVerification(false, 'Service account credentials file not found at: ' . $credentialsPath, $configuration);
        return false;
    }

    // Set the environment variable for authentication
    putenv("GOOGLE_APPLICATION_CREDENTIALS={$credentialsPath}");

        $this->insertVerification(true, 'Service account credentials file found at: ' . $credentialsPath, $configuration);
        return true;
    }

    protected function insertVerification($is_success, $reason, $configuration)
    {
        $verification = new Verification();
        $verification->is_success = $is_success;
        $verification->reason = $reason;
        $verification->configuration_id = $configuration->id;
        $verification->save();
    }
}
