<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Filament\Resources\BillResource\RelationManagers;
use App\Models\Bill;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Hugomyb\FilamentMediaAction\Tables\Actions\MediaAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Storage;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use Joaopaulolndev\FilamentPdfViewer\Infolists\Components\PdfViewerEntry;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use ZeeshanTariq\FilamentAttachmate\Forms\Components\AttachmentFileUpload;

class BillResource extends Resource
{
    protected static ?string $model = Bill::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected function handleRecordCreation(array $data): Bill
{
    dd($data);
    return static::getModel()::create($data);
}


    public static function form(Form $form): Form
    {
        $fields = [
            Forms\Components\Select::make('provider_id')
                ->required()
                ->preload()
                ->searchable()
                ->native(false)
                ->relationship('provider', 'name'),
            Forms\Components\TextInput::make('bill_number')
                ->required()
                ->maxLength(255),
                FileUpload::make('file_url')
                ->label('PDF File')
                ->acceptedFileTypes(['application/pdf'])
                ->hidden(fn (Get $get) => $get('operation') === 'view')
                ->columnSpanFull()

                ->reactive()
                ->required(),
                PdfViewerField::make('file_url')
                ->label('PDF Preview')    
                ->hidden(function ($operation){
                    
                    return $operation !== 'view';
                })

            ->columnSpanFull()    
            ->minHeight('80svh')
            ->required(),
        ] ;

         if($form->getOperation() === 'view'){
            
        } 
        
         $form
            ->schema($fields)
            ;

        
            return $form;
    }

/*     public static function infolist(Infolist $infolist): Infolist
{
    return $infolist
        ->schema([
            // for provider name
            TextEntry::make('provider.name')
                ->label('Provider Name'),
            // for bill number
            TextEntry::make('bill_number')
                ->label('Bill Number'),
            // for bill date
            TextEntry::make('bill_date')
                ->label('Bill Date'),
            // for pdf viewer
            PdfViewerEntry::make('file_url')
                ->label('View the PDF')
                ->minHeight('40svh')
        ]);
} */

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('provider.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('bill_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bill_date')
                    ->date()
                    ->sortable(),
               /*  Tables\Columns\TextColumn::make('file_url')
                    ->searchable(), */
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                MediaAction::make('file_url')
                ->label('View PDF')
                ->media(fn($record) => Storage::url($record->file_url))
                ->autoplay()    
                ->icon('hugeicons-file-view')
                ->preload(false),
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
            'index' => Pages\ListBills::route('/'),
            'create' => Pages\CreateBill::route('/create'),
            'view' => Pages\ViewBill::route('/{record}'),
            'edit' => Pages\EditBill::route('/{record}/edit'),
        ];
    }

    
}
