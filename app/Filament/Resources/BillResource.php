<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\BillResource\Pages;
use App\Filament\Resources\BillResource\RelationManagers;
use App\Forms\Components\DisplayDocAi;
use App\Forms\Components\ImageAiField;
use App\Models\Bill;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\ViewField;
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
use Google\Cloud\DocumentAI\V1\Document;

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
                Forms\Components\Hidden::make('file_url'),

                FileUpload::make('image_url')
                ->previewable()
                ->directory('bills')
                ->visibility('public')
                ->label('Image File')
                ->acceptedFileTypes(['image/*'])
                ->image()
                ->multiple(true)
                ->afterStateUpdated(function(Set $set, Get $get, ?array $state){
                    /** @var TemporaryUploadedFile[] $tempFiles */
                    $tempFiles = $state;
                    $data = [
                        'title' => 'Welcome to ItSolutionStuff.com',
                        'date' => date('m/d/Y'),
                        'images' => $tempFiles
                    ]; 
                    
                          
                    $pdf = Pdf::loadView('myPDF', $data);
                    // dd($pdf);
                    $randomNamedPdf = 'myPDF-' . time() . '.pdf';
                    $pdf->save(storage_path('app/private/livewire-tmp/' . $randomNamedPdf));
                    // $pdfUrl = route('temporary-file.serve', ['filename' => 'myPDF.pdf']);
                    $set('file_url', array($randomNamedPdf));
                    
                    
                   //dd($pdfUrl);
                })
                ->hidden(fn (Get $get) => $get('operation') === 'view' || $get('file_type') === 'pdf' || $get('file_type') === null)
                ->columnSpanFull()
                ->imageEditor()
                ->reactive()
                ->required(), 
                // action button to generate datas with ai 
                
                /* PdfViewerField::make('file_preview')
                ->reactive()
                ->visibility('private')
                ->fileUrl(function($record, Get $get, $operation, $state){

                    if($operation === 'edit'){

                        if($get('file_url') === null || empty($get('file_url'))){
                            return '';
                        }
                        $fileUrls = $get('file_url');
                        $fileUrl = $fileUrls[array_key_first($fileUrls)];
                        if($fileUrl !== $record->file_url){
                          
                            $tempFile = $fileUrl;
                            
                            $temporaryUrl = route('temporary-file.serve', ['filename' => basename($tempFile->getClientOriginalPath())]);
                            return $temporaryUrl;
                        }else{
                            return url('storage/bills/' . basename($record->file_url));
                        }

                            
                    }else if ($operation === 'view'){
                        
                        return url('storage/bills/' . basename($record->file_url));
                    }else{
                        if($get('file_url') === null || empty($get('file_url'))){
                           return '';
                        }else{

                           
                            $file_url_key = array_key_first($get('file_url'));
                            $file_url = $get('file_url')[$file_url_key];
                            $temporaryUrl = route('temporary-file.serve', ['filename' => basename($file_url), 'isPrivate' => false]);
                            // dd($temporaryUrl);

                            $fullPath = storage_path('app/public/' . $file_url);
                            // dd($fullPath);
                            // dd($fullPath);
                            return $temporaryUrl; 
                    }
                    }

                })

                ->label('PDF Preview')    
                
                ->hidden(function ($operation, Get $get, $record){
                  
                    return false;
                })
                

            ->columnSpanFull()    
            ->minHeight('80svh'), */
            
            Section::make('Data from AI')
            ->schema([
        DisplayDocAi::make('data_for_img'),
            ]),
        
            Section::make('Line Items')
            ->schema([
            Repeater::make('line_items')
            
                ->schema([
                    TextInput::make('quantity')->required(),
                    TextInput::make('unit_price')->required(),
                    Select::make('product')
                    ->options(Product::all()->pluck('name', 'id'))
                    ->required(),
                ])
    ->columns(4)
                ]),])
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

    private function getImagesWithDoc(Document $document){
        $pages = $document->getPages();
        $images = [];
        foreach($pages as $page){
            $images[] = $page->getImage();
        }
        return $images;
    }

    
}
