<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'lucide-package-search';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {

        $selectedProviders = $form->getRawState()['products_code_by_provider'] ?? [];
        // filter to make sure not null
        $selectedProviders = array_filter($selectedProviders, function($provider){
            return $provider['provider_id'] !== null;
        });
        $providersIds = [];
        if(count($selectedProviders) > 0){
            foreach($selectedProviders as $provider){
                $providersIds[] = $provider['provider_id'];
            }
        }

      


        
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('description')
                    ->required()
                    ->maxLength(255),
                Forms\Components\FileUpload::make('image')
                    ->image(),
                # field to link many to many relationship with provider
                /* Forms\Components\Select::make('providers')
                    ->relationship('providers', 'name')
                    ->multiple()
                    ->preload(), */
                Hidden::make('selected_providers')
                    ->default([]),
                Repeater::make('products_code_by_provider')
                ->reactive()
                ->afterStateUpdated(
                    function ($state, Set $set, Get $get) {
                        $set('selected_providers', $state);
                        
                    }
                )
                    ->schema(
                        [
                            Forms\Components\Select::make('provider_id')
                            
                                
                                ->required()
                                ->native(false)
                                ->preload()
                                ->searchable()
                                ->preload()
                                ->reactive()
                                ->validationAttribute('Provider')
                                
                                
                                
                                ->options(function ($state, Get $get, $component) use ($providersIds) {
                                    $query = Provider::query();
                                    if($state){ 
                                        $query->where('id', $state);
                                    }else{
                                        $query->whereNotIn('id', $providersIds);
                                    }
                                    return $query->pluck('name', 'id')->toArray();
                                }) 
                                
                                ,
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->maxLength(255),
                    ]
                )
                ->columns(2)
                ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ImageColumn::make('image'),
                # field to link many to many relationship with provider
                Tables\Columns\TextColumn::make('products_code_by_provider.provider_id')
                    ->label('Providers')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        return count($record->products_code_by_provider->pluck('provider_id')->toArray());
                    }),
            ])
            ->filters([
                # filter by provider
                Tables\Filters\SelectFilter::make('providers')
                    ->relationship('providers', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
