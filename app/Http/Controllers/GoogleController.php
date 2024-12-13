<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        dd('redirectToGoogle');
        return Socialite::driver('google')
    ->scopes([
        'https://www.googleapis.com/auth/cloud-platform', // Required for listing Google Cloud projects
        'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
        ])
        ->redirect();
    }

    public function handleGoogleCallback()
    {
        dd('handleGoogleCallback');
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'access_token' => $googleUser->token,
                'refresh_token' => $googleUser->refreshToken,
            ]
        );

        Auth::login($user);

        return redirect('/dashboard'); // or any Filament route
    }
}
