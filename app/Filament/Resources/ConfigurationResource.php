<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ConfigurationResource\Pages;
use App\Filament\Resources\ConfigurationResource\RelationManagers;
use App\Http\Controllers\VerificationController;
use App\Models\Configuration;
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
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;

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
                        Tabs\Tab::make('Google Credential Key')
                            ->icon('heroicon-o-key')
                            ->schema([
                                FileUpload::make('key_path')
                                    ->label('Google Credential Key')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        /** @var TemporaryUploadedFile $tempFile */
                                        $tempFile = $state;
                                        $jsonContent = file_get_contents($tempFile->getRealPath(), true);
                                        $set('json', $jsonContent);
                                    })
                                    ->multiple(false)
                                    ->required()
                                    ->disk('local')
                                    ->getUploadedFileNameForStorageUsing(fn () => 'google-credential-key/key.json')
                                    ->directory('google-credential-key')
                                    ->preserveFilenames()
                                    ->acceptedFileTypes(['application/json']),
                                PrettyJson::make('json')
                                    ->afterStateHydrated(function (Set $set, Get $get, string $operation) {
                                        if (in_array($operation, ['view', 'edit'])) {
                                            $filePath = array_key_first($get('key_path'));
                                            if (!$filePath) {
                                                return;
                                            }
                                            $jsonContent = file_get_contents(storage_path('app/private/' . $get('key_path')[$filePath]), true);
                                            $set('json', $jsonContent);
                                        }
                                    })
                                    ->hidden(fn (Get $get) => !$get('key_path'))
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
                                                $verifyController = new VerificationController();
                                                $isSuccess = $verifyController->verify();
                                                if ($isSuccess) {
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
                                        ->orderColumn('id')
                                        

                                    ->relationship('verifications')
                                    ->schema([
                                        Toggle::make('is_success')
                                    ])
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
