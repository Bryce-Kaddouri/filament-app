<?php

namespace App\Filament\Resources;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use Filament\Tables\Filters\Filter;
use Google\Protobuf\Internal\RepeatedField;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

     public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name'),
                TextInput::make('projectId'),
                TextInput::make('projectNumber'),
                TextInput::make('createTime'),
                TextInput::make('firebase'),
                Section::make('Enabled Services')
                ->schema([
                    Repeater::make('enabledServices')
                    
                    
                    ->schema([
                        TextInput::make('name')
                        ,
                        TextInput::make('title')
                        ,
                    ]),
                ]),
            ]);
    } 

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                ->searchable(),
                Tables\Columns\TextColumn::make('projectId')
                ->searchable(),
                Tables\Columns\TextColumn::make('projectNumber')
                ->searchable(),
                Tables\Columns\TextColumn::make('createTime'),
                Tables\Columns\IconColumn::make('firebase')
                ->icon(function ($record) {
                    return $record->firebase ? 'si-firebase' : 'si-googlecloud';
                }),
            ])
            ->filters([
                // slect fileter 
                SelectFilter::make('firebase')
                ->query(function (Builder $query, $state): Builder {
                    
                    if($state['value'] == 'true'){
                        
                        return $query->where('firebase', true);
                    }elseif($state['value'] == 'false'){
                        return $query->where('firebase', false);
                    }
                    else{
                        return $query;
                    }
                })
                ->options([
                    'true' => 'Firebase',
                    'false' => 'Google Cloud',
                ]),
                // date filter
                Filter::make('created_at')
    ->form([
        DatePicker::make('created_from'),
        DatePicker::make('created_until'),
    ])
    ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when(
                $data['created_from'],
                fn (Builder $query, $date): Builder => $query->whereDate('createTime', '>=', $date),
            )
            ->when(
                $data['created_until'],
                fn (Builder $query, $date): Builder => $query->whereDate('createTime', '<=', $date),
            );
    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /* public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(2)
            ->schema([
                TextEntry::make('name'),
                TextEntry::make('projectId'),
                TextEntry::make('projectNumber'),
                TextEntry::make('createTime'),
                TextEntry::make('firebase'),
                Section::make('Enabled Services')
                ->schema([
                    RepeatableEntry::make('enabledServices')
                    
                    
                    ->schema([
                        TextEntry::make('name')
                        ,
                        TextEntry::make('title')
                        ,
                    ]),
                ]),
            ]);
    }  */

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'view' => Pages\ViewProjects::route('/{record}'),
        ];
    }
}
