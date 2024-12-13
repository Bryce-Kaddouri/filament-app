<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\ProviderLineChartRessource\Widgets\ProviderLineChart;
use App\Filament\Resources\ProviderPriceByMonthRessourceResource\Widgets\TrendProviderPriceByMonth;
use App\Providers\Filament\AvatarProviders\BoringAvatarsProvider;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use App\Filament\Pages\Auth\EditProfile;
use App\Models\User;
use Edwink\FilamentUserActivity\FilamentUserActivityPlugin;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;
use DutchCodingCompany\FilamentSocialite\FilamentSocialitePlugin;
use DutchCodingCompany\FilamentSocialite\Provider;
use Filament\Support\Colors;
use Laravel\Socialite\Contracts\User as SocialiteUserContract;
use Illuminate\Contracts\Auth\Authenticatable;
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('/')
            ->login()
            ->registration()
            ->emailVerification()
            ->passwordReset()
            ->viteTheme('resources/css/filament/admin/theme.css')
            // ->profile(EditProfile::class, isSimple: false)          
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(function () {
                        // dd(auth()->user()->name);
                        return auth()->guard('web')->user()->name;
                    })
                    ->url(fn (): string => EditProfilePage::getUrl())
                    ->icon('heroicon-m-user-circle')
                    ->visible(true)
                    //If you are using tenancy need to check with the visible method where ->company() is the relation between the user and tenancy model as you called
                    ,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentEditProfilePlugin::make()
                
                
                ->setIcon('heroicon-o-user')
                ->shouldShowAvatarForm(
                    value: true,
                    directory: 'avatars', // image will be stored in 'storage/app/public/avatars
                    rules: 'mimes:jpeg,png|max:1024' //only accept jpeg and png files with a maximum size of 1MB
                )
                ->shouldShowBrowserSessionsForm()
                ->setSort(8)
                ->shouldShowDeleteAccountForm(value: true)
                ->customProfileComponents([
                    \App\Livewire\CustomProfileComponent::class,
                ]),
                FilamentUserActivityPlugin::make(),
                FilamentSocialitePlugin::make()
                
                // (required) Add providers corresponding with providers in `config/services.php`.
                ->providers([
                    // Create a provider 'gitlab' corresponding to the Socialite driver with the same name.
                    Provider::make('google')
                    ->with([
        'access_type' => 'offline', // Requests a refresh token
        'prompt' => 'consent',     // Ensures consent screen is shown
    ])
                
                
                        ->stateless()

                        ->label('Google')
                        ->icon('fab-google')
                        ->color(Color::hex('#DB4437'))
                        ->outlined(true)
                        ->stateless(true)
                        ->scopes([
                            'https://www.googleapis.com/auth/cloud-platform',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile',
        'https://www.googleapis.com/auth/devstorage.full_control',
                        ])
                ])
                
                ->createUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
                    // create user in database
                    return  User::create([
                        'name' => $oauthUser->getName(),
                        'email' => $oauthUser->getEmail(),
                        'avatar' => $oauthUser->getAvatar(),
                        'access_token' => $oauthUser->token,
                        'refresh_token' => $oauthUser->refreshToken,
                    ]);

                })
                ->resolveUserUsing(function (string $provider, SocialiteUserContract $oauthUser, FilamentSocialitePlugin $plugin) {
                     //dd($oauthUser, 'oauthUser', 'access_token', $oauthUser->token, 'refresh_token', $oauthUser->refreshToken);
                    // check if user exists in database
                    $user = User::where('email', $oauthUser->getEmail())->first();
                    if (!$user) {
                        $user = User::create([
                            'name' => $oauthUser->getName(),
                            'email' => $oauthUser->getEmail(),
                            'avatar_url' => $oauthUser->getAvatar(),
                            'access_token' => $oauthUser->token,
                            'refresh_token' => $oauthUser->refreshToken,
                        ]);
                    }else{
                        $user->update([
                            'access_token' => $oauthUser->token,
                            'refresh_token' => $oauthUser->refreshToken,
                        ]);
                    }
                    // dd($user, 'user');
                    return $user;
                }) 
                
               
                // (optional) Enable/disable registration of new (socialite-) users.
                ->registration(true)
                ->domainAllowList(['localhost', '127.0.0.1', 'gmail.com'])
               
                // (optional) Enable/disable registration of new (socialite-) users using a callback.
                // In this example, a login flow can only continue if there exists a user (Authenticatable) already.
                //->registration(fn (string $provider, SocialiteUserContract $oauthUser, ?Authenticatable $user) => (bool) $user)



                
            ]);
    }
}
