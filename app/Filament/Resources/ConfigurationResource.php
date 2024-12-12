<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfigurationResource\Pages;
use App\Filament\Resources\ConfigurationResource\RelationManagers;
use App\Forms\Components\IconField;
use App\Forms\Components\LogViewer;
use App\Http\Controllers\GcloudController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\VerificationController;
use App\Models\Configuration;
use App\Models\Project;
use App\Models\ServiceAccount;
use BladeUI\Icons\Components\Icon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Pages\ViewRecord;
use Novadaemon\FilamentPrettyJson\PrettyJson;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;

class ConfigurationResource extends Resource
{
    protected static ?string $model = Configuration::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // update query to order by created date
    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('Steps')
                        ->icon('heroicon-o-key')
                        ->schema([
                            Wizard::make([
                                Wizard\Step::make('Project Informations')
                                    ->schema([
                                        Select::make('project_id')
                                        ->afterStateUpdated(function (Set $set, Get $get, string $operation) {
                                            $set('service_account_id', null);
                                        })
                                        ->reactive()
                                        ->live()
                                        ->label('Project ID')
                                        ->searchable()
                                        ->native(false)
                                        ->preload()
                                        ->suffixAction(
                                            Action::make('createProject')
                                                ->icon('heroicon-o-plus')
                                                ->form( 
                                                   [
                                                    TextInput::make('new_project_id')
                                                    ->label('New Project ID')
                                                    ->required(),
                                                   ]
                                                )
                                               
                                               
                                                ->action(function (array $data, Action $action) {
                                                    $projectController = new ProjectController();
                                                    $response = $projectController->createProject($data['new_project_id'] );
                                                    $response = json_decode($response, true);
                                                    if($response['status'] == 'success'){
                                                        // success notification
                                                        Notification::make()
                                                            ->title('Success')
                                                            ->body('Project created successfully.')
                                                            
                                                            ->success()
                                                            ->send();
                                                    }else{
                                                        // error notification
                                                        Notification::make()
                                                        
                                                            ->title('Error')
                                                            ->body($response['result'])
                                                            ->danger()
                                                            ->send();
                                                            // avoid closing the modal
                                                            $action->halt();
                                                    }
                                                })
                                        )
                                        ->options(Project::all()->pluck('name', 'projectId'))
                                        ->required(),
                                        Select::make('service_account_id')
                                        ->suffixAction(
                                            Action::make('createServiceAccount')
                                                ->icon('heroicon-o-plus')
                                                ->form( 
                                                   [
                                                    TextInput::make('display_name')
                                                    ->label('Service Account Name')
                                                    ->required(),
                                                   ]
                                                )
                                        )
                                        ->reactive()
                                        ->live()
                                        ->label('Service Account ID')
                                        ->searchable()
                                        ->native(false)
                                        ->preload()
                                        ->options(function ($get) {
                                            $projectId = $get('project_id');
                                            if ($projectId) {
                                                //dd($projectId);
                                                $projectController = new ProjectController();

                                                $servicesAccounts = $projectController->listServicesAccounts($projectId);
                                                $servicesAccounts = json_decode($servicesAccounts, true);
                                                // dd($serviceAccount);
                                                return collect($servicesAccounts)->pluck('name', 'uniqueId');
                                            }
                                            return [];
                                        })
                                        ->required(),
                                    ]),
                                Wizard\Step::make('Billing Account')
                                    ->schema([
                                        Select::make('billing_account_id')
                                        ->prefixActions([
                                            Action::make('createBillingAccount')
                                                ->icon('heroicon-o-check-circle')
                                                ->form( 
                                                   [
                                                    TextInput::make('billing_account_id')
                                                    ->label('Billing Account ID')
                                                    ->required(),
                                                   ]
                                                )
                                        ])
                                        
                                        ->label('Billing Account ID')
                                        ->searchable()
                                        ->native(false)
                                        ->preload()
                                        ->options(function ($get) {
                                            $projectId = $get('project_id');
                                            $projectController = new ProjectController();
                                            $billingAccounts = $projectController->listBillingAccounts($projectId);
                                            $billingAccounts = json_decode($billingAccounts, true);
                                            return collect($billingAccounts)->pluck('name', 'billingAccountId');
                                        })
                                    ]),
                                Wizard\Step::make('Billing')
                                    ->schema([
                                        // ...
                                    ]),
                            ])->persistStepInQueryString()
                        ]),
                        Tabs\Tab::make('Google Credential Key')
                            ->icon('heroicon-o-key')
                            ->schema([
                                Select::make('project_id')
                                ->label('Project ID')
                                ->searchable()
                                ->native(false)
                                ->preload()
                                ->options(Project::all()->pluck('name', 'projectId'))
                                ->required(),
                               
                                PrettyJson::make('json')
                                    ->afterStateHydrated(function (Set $set, Get $get, string $operation) {
                                        // check if key exists in storage/app/private/google-credential-key/key.json
                                        if (Storage::disk('local')->exists('google-credential-key/key.json')) {
                                            // dd('exists', Storage::disk('local')->get('google-credential-key/key.json'));
                                            $jsonString = Storage::disk('local')->get('google-credential-key/key.json');
                                           // dd($json);
                                            $set('json', $jsonString);
                                        }else{
                                            // dd('not exists');
                                            $set('json', null);
                                        }
                                    })
                            ]),
                            Tabs\Tab::make('Permissions Verification')
                                
                                ->hidden(fn ($operation) => $operation !== 'view')
                                ->icon('heroicon-o-check-circle')
                                ->schema([
                                    Section::make('Permissions Verification')
                                    ->headerActions([
                                        Action::make('verify')
                                            ->icon('heroicon-o-check-circle')
                                            // ->color('danger')
                                            // ->requiresConfirmation()
                                            ->action(function ($record) {
                                                $currentUrl = static::getUrl(
                                                    parameters: [
                                                        'tab' => '-permissions-verification-tab'
                                                    ]
                                                );
                                                $gCloudController = new GcloudController();
                                                $isSuccess = $gCloudController->verifyServiceAccount();
                                                if ($isSuccess->is_success) {
                                                    // success notification
                                                    Notification::make()
                                                        ->title('Success')
                                                        ->body('Verification completed successfully.')
                                                        ->success()
                                                        ->send();
                                                }else{
                                                    // error notification
                                                    Notification::make()
                                                        ->title('Error')
                                                        ->body('Verification failed.')
                                                        ->danger()
                                                        ->send();
                                                }
                                                // refresh the page
                                                
                                                // dd($currentUrl);
                                                return redirect($currentUrl);
                                            })
                                            // ->url(fn () => route('verify'))
                                    ])
                                    ->schema([
                                        Repeater::make('verifications')
                                        ->columns(7)
                                        ->orderColumn('id')
                                    ->relationship('verifications')
                                    ->schema([
                                        IconField::make('is_success')
                                        ->label('Status')
                                        ->columnSpan(1),
                                        DateTimePicker::make('created_at')
                                        ->label('Date and Time')
                                        ->columnSpan(2), 
                                        Textarea::make('reason')
                                        ->label('Reason')
                                        ->columnSpan(4),
                                        


                                            
                                    
                                    ])
                                    ])
                                    ]),
                                    Tabs\Tab::make('Create Service Account')
                                
                                ->icon('heroicon-o-check-circle')
                                ->schema([
                                    Section::make('Create Service Account')
                                    ->schema([
                                        TextInput::make('display_name')
                                        ->helperText('The name of the service account to create. must be unique and must be formated like this: my-account-number-1')
                                        ->label('Service Account Name')
                                    ])
                                    ->footerActions([
                                        Action::make('createServiceAccount')
                                            ->icon('heroicon-o-check-circle')
                                            //->requiresConfirmation()
                                            ->action(function (Get $get) {
                                                $gcloudController = new GcloudController();
                                                $serviceAccount = $gcloudController->createServiceAccount($get('display_name'), $get('project_id'));
                                            }),
                                            
                                        ]),
                                    LogViewer::make('log_viewer')
                                                ->label('Log Viewer')
                                                
                                ]),
                                    Tabs\Tab::make('List Processors')
                                
                                ->hidden(fn ($operation) => $operation !== 'view')
                                ->icon('heroicon-o-check-circle')
                                ->schema([
                                    Section::make('List Processors')
                                    ->headerActions([
                                        Action::make('listProcessors')
                                            ->icon('heroicon-o-check-circle')
                                            ->action(function ($record) {
                                                $verifyController = new VerificationController();
                                                $verifyController->listProcessors();
                                            })
                                    ])
                                    ->schema([
                                        TextInput::make('project_id')
                                        ->label('Project ID')
                                    ])
                                ])
                            
                    ])
                    ->persistTabInQueryString()
            ])

            
            ->columns(1);
    }


    public static function getRelations(): array
    {
        return [
            
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ViewConfiguration::route('/'),
            'edit' => Pages\EditConfiguration::route('/{record}/edit'),
        ];
    }
}
