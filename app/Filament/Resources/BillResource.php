<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BillResource\Pages;
use App\Filament\Resources\BillResource\RelationManagers;
use App\Models\Bill;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
                Forms\Components\DatePicker::make('bill_date')
                ->native(false)
                
                ->required(),
                Section::make('File Upload')
                ->schema([
                Select::make('file_type')
                ->options([
                    'pdf' => 'PDF',
                    'image' => 'Image',
                ])
                ->reactive()
                ->required(),
                FileUpload::make('file_url')
                ->storeFiles(true)
                ->directory('bills')
                ->visibility('private')
                ->label('PDF File')
                ->acceptedFileTypes(['application/pdf'])
                ->multiple(false)
                ->openable()
                ->downloadable()
                ->hidden(fn (Get $get) => $get('operation') === 'view' || $get('file_type') === 'image' || $get('file_type') === null)
                ->columnSpanFull()
                ->reactive()
                ->required(),
                FileUpload::make('file_url')
                ->previewable()
                ->directory('bills')
                ->visibility('public')
                ->label('Image File')
                ->acceptedFileTypes(['image/*'])
                ->image()
                ->multiple(true)
                
                ->hidden(fn (Get $get) => $get('operation') === 'view' || $get('file_type') === 'pdf' || $get('file_type') === null)
                ->columnSpanFull()
                ->imageEditor()
                ->reactive()
                ->required(), 
                PdfViewerField::make('file_preview')
                ->reactive()
                ->visibility('private')
                ->fileUrl(function($record, Get $get, $operation){
                    if($operation === 'edit'){

                        if($get('file_url') === null || empty($get('file_url'))){
                            return '';
                        }
                        $fileUrls = $get('file_url');
                        $fileUrl = $fileUrls[array_key_first($fileUrls)];
                        // check if file has changed
                        if($fileUrl !== $record->file_url){
                            /** @var TemporaryUploadedFile $tempFile */
                            $tempFile = $fileUrl;
                            // dd($tempFile->getRealPath());
                            //dd($tempFile->getClientOriginalPath());
                            // dd(Storage::url($tempFile->getClientOriginalPath()));
                            $temporaryUrl = route('temporary-file.serve', ['filename' => basename($tempFile->getClientOriginalPath())]);
                            // dd($temporaryUrl);
                            return $temporaryUrl;
                            // return 'http://localhost:8000/storage/app/private/livewire-tmp/8qZArOpjyzlYvuDaQigH95V1kmazgM-metaSW52b2ljZS1DVkRKTE8tMDAwMDMucGRm-.pdf';
                        }else{
                            return url('storage/bills/' . basename($record->file_url));
                        }

                            
                    }else if ($operation === 'view'){
                        
                        return url('storage/bills/' . basename($record->file_url));
                    }else{
                        if($get('file_url') === null || empty($get('file_url'))){
                           return '';
                        }else{
                            // dd($get('file_url'));
                            $fileUrl = $get('file_url');
                            $fileUrl = $fileUrl[array_key_first($fileUrl)];
                            // dd($fileUrl);


                        // check if file has changed
                          /** @var TemporaryUploadedFile $tempFile */
                          $tempFile = $fileUrl;
                          // dd($tempFile->getRealPath());
                          //dd($tempFile->getClientOriginalPath());
                          // dd(Storage::url($tempFile->getClientOriginalPath()));
                          $temporaryUrl = route('temporary-file.serve', ['filename' => basename($tempFile->getClientOriginalPath())]);
                          // dd($temporaryUrl);
                          return $temporaryUrl;
                       
                    }
                    }
                    
                })

                ->label('PDF Preview')    
                
                ->hidden(function ($operation, Get $get, $record){
                    if($get('file_type') === 'pdf'){
                        if($operation === 'edit' && array_key_first($get('file_url')) !== null){
                           
                            $fileUrl = $get('file_url');
                            
                            
                            if($fileUrl !== $record->file_url){
                                return false;
                            }
                        }
                    }
                    return ($operation !== 'view' || $operation !== 'edit') && $get('file_type') !== 'pdf';
                })
                

            ->columnSpanFull()    
            ->minHeight('80svh')
            ,
            ]),
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
