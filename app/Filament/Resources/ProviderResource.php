<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderResource\Pages;
use App\Filament\Resources\ProviderResource\RelationManagers;
use App\Models\Provider;
use Cheesegrits\FilamentGoogleMaps\Actions\StaticMapAction;
use CodeWithDennis\SimpleMap\Components\Forms\SimpleMap as FormsSimpleMap;
use CodeWithDennis\SimpleMap\SimpleMap;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Tapp\FilamentGoogleAutocomplete\Forms\Components\GoogleAutocomplete;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Cheesegrits\FilamentGoogleMaps\Columns\MapColumn;
use Cheesegrits\FilamentGoogleMaps\Filters\RadiusFilter;
use Ysfkaya\FilamentPhoneInput\Forms\PhoneInput;
use Ysfkaya\FilamentPhoneInput\Tables\PhoneColumn;
use Ysfkaya\FilamentPhoneInput\Infolists\PhoneEntry;
use Ysfkaya\FilamentPhoneInput\PhoneInputNumberType;
use libphonenumber\PhoneNumberType;
use Filament\Forms\Components\Fieldset;
use Illuminate\Support\Facades\Http;

class ProviderResource extends Resource
{
    protected static ?int $navigationSort = 2;

    public static function getNavigationSort(): ?int
    {
        return 2;
    }



    protected static ?string $model = Provider::class;

    protected static ?string $navigationIcon = 'lucide-user-round-search';

    public static function form(Form $form): Form
{

  
    return $form
        ->schema([
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->required()
                ->maxLength(255),
            /* Forms\Components\TextInput::make('phone')
                ->tel()
                ->required()
                ->maxLength(255), */
                PhoneInput::make('phone')
                ->ipLookup(function () {
                    return rescue(fn () => Http::get('https://ipinfo.io/json')->json('country'), app()->getLocale(), report: false);
                })
                ->validateFor(
                   
                    type: PhoneNumberType::MOBILE | PhoneNumberType::FIXED_LINE,
                    lenient: true, // default: false
                )
                ->required(),
             // Color Picker
             Forms\Components\ColorPicker::make('color')
             ->required()
             ->reactive()
             ->afterStateHydrated(function ($component, $record) {
                 if ($record) {
                     $hexColor = sprintf("#%02x%02x%02x", $record->red, $record->green, $record->blue);
                     $component->state($hexColor);
                 }
             })
             ->afterStateUpdated(function (callable $set, $state) {
                 $red = hexdec(substr($state, 1, 2));
                 $green = hexdec(substr($state, 3, 2));
                 $blue = hexdec(substr($state, 5, 2));
                 $set('red', $red);
                 $set('green', $green);
                 $set('blue', $blue);
             }),

         // Hidden inputs to hold RGB values
         Forms\Components\Hidden::make('red'),
         Forms\Components\Hidden::make('green'),
         Forms\Components\Hidden::make('blue'),
                Forms\Components\FileUpload::make('image')
                ->columnSpan(2)
                ->image(),
        
            Fieldset::make('Address')
            /* ->afterStateUpdated(function($state, $set){
                dd($state, $set);
            }) */
            ->schema([
            Forms\Components\TextInput::make('full_address')
                ->columnSpan(2)
                ->required()
                ->maxLength(255),
                Forms\Components\TextInput::make('street')
                ->required()
                ->readOnly()
                ->maxLength(255),
                Forms\Components\TextInput::make('city')
                ->required()
                ->readOnly()
                ->maxLength(255),
                Forms\Components\TextInput::make('state')
                ->required()
                ->readOnly()
                ->maxLength(255),
                Forms\Components\TextInput::make('zip')
                ->required()
                ->readOnly()
                ->maxLength(255),
                Map::make('location')
                ->mapControls([
                    'mapTypeControl'    => false,
                    'scaleControl'      => true,
                    'streetViewControl' => false,
                    'rotateControl'     => false,
                    'fullscreenControl' => false,
                    'searchBoxControl'  => false, // creates geocomplete field inside map
                    'zoomControl'       => true,
                ])
                
                ->defaultZoom(15) // default zoom level when opening form
                ->autocomplete('full_address') // field on form to use as Places geocompletion field
                ->autocompleteReverse(true)
                ->columnSpan(2) // reverse geocode marker location to autocomplete field
                ->reverseGeocode([
                    'street' => '%n %S',
                    'city' => '%L',
                    'state' => '%A1',
                    'zip' => '%z',
                ]) // reverse geocode marker location to form fields, see notes below
                ->debug() // prints reverse geocode format strings to the debug console 
               // ->defaultLocation([39.526610, -107.727261]) // default for new forms
                ->draggable(false) // allow dragging to move marker
                ->clickable(false) // allow clicking to move marker
                ->geolocate(true) // adds a button to request device location and set map marker accordingly
                ->geolocateLabel('Get Location') // overrides the default label for geolocate button
                ->geolocateOnLoad(false, false), // geolocate on load, second arg 'always' (default false, only for new form))
                ])
                ->columnSpan(2),
               
                
                

            
        ]);
}



    public static function table(Table $table): Table
    {
        
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                /* Tables\Columns\TextColumn::make('phone')
                    ->searchable(), */
                    PhoneColumn::make('phone')->displayFormat(PhoneInputNumberType::NATIONAL),

                    
                
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ColorColumn::make('color')
                    ->label('Color')
                    ->getStateUsing(function ($record) {
                        $red = $record->red ?? 255;
                        $green = $record->green ?? 0;
                        $blue = $record->blue ?? 0;
                        return sprintf('#%02x%02x%02x', $red, $green, $blue);
                    }),
                    Tables\Columns\TextColumn::make('products.name')
                    ->label('Products')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return count($record->products->pluck('name')->toArray());
                    }),
            ])
            ->filters([
                RadiusFilter::make('radius')
        ->latitude('lat')  // optional lat and lng fields on your table, default to the getLatLngAttributes() method
        ->longitude('lng') // you should have one your model from the fgm:model-code command when you installed
        ->selectUnit() // add a Kilometer / Miles select
        ->kilometers() // use (or default the select to) kilometers (defaults to miles)
        ->section('Radius Search') // optionally wrap the filter in a section with heading

            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    StaticMapAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProviders::route('/'),
            'create' => Pages\CreateProvider::route('/create'),
            'view' => Pages\ViewProvider::route('/{record}'),
            'edit' => Pages\EditProvider::route('/{record}/edit'),
        ];
    }
}
