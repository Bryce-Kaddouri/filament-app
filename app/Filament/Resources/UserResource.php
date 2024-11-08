<?php

namespace App\Filament\Resources;

use App\Enums\RoleUserEnum;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'lucide-users';

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
                    ->maxLength(255)
                    ->unique(table: User::class, column: 'email'),
                // Forms\Components\DateTimePicker::make('email_verified_at')->hidden(fn(Get $get): bool => $get('role') !== 'admin'),
                Forms\Components\TextInput::make('password')
                    ->confirmed()
                    ->password()
                    ->required()
                    ->maxLength(255)
                    ->autocomplete('new-password')
                    ->revealable()
                    ->hidden(function (Get $get) use ($form): bool {
                        dd($form);
                        dd($get);
                        dd($get('action'));
                        return $get('action') === 'view';
                    }),
                // confirm password
                Forms\Components\TextInput::make('password_confirmation')
                    ->password()
                    ->required()
                    ->revealable()
                    ->hidden(fn(Get $get): bool => $get('action') === 'view'),
                    Forms\Components\Select::make('role')
                    ->native(false)
                    ->options([
                        RoleUserEnum::ROLE_USER->value => 'User',
                        RoleUserEnum::ROLE_ADMIN->value => 'Admin',
                    ])
                    ->required()
                    ->enum(RoleUserEnum::class),
                    
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('role'),
            ])
            ->filters([
                //
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
