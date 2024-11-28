<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\BillResource\Pages;
use App\Filament\Resources\BillResource\RelationManagers;
use App\Forms\Components\DisplayDocAi;
use App\Forms\Components\ImageAiField;
use App\Http\Controllers\BillAiController;
use App\Models\Bill;
use App\Models\Product;
use App\Services\ParsedImage;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
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
use Hugomyb\FilamentMediaAction\Forms\Components\Actions\MediaAction as FormMediaAction;
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

        $fileUploadActions = [
               
            
        ];

         if(isset($form->getRawState()['file_url'])){
            $fileUploadActions[] = FormMediaAction::make('file_url')
            ->label('View PDF')
            ->media(function($state){
               // check if is array 
               if(is_array($state)){
                 // check if is type of TemporaryUploadedFile
                 $firstElement = array_key_first($state);
                 if($state[$firstElement] instanceof TemporaryUploadedFile){
                    // dd($state, 'if');
                    // use route to serve the file
                    //dd($state[$firstElement]);
                     // dd($state[$firstElement]->getFilename());
                    $url = route('temporary-file.serve', ['filename' => $state[$firstElement]->getFilename(), 'isPrivate' => true]);
                    // dd($url);
                    return $url;
                }else{
                    
                    dd($state, 'else', 'runtype', gettype($state));
                    return Storage::url($state);
                }
               }else{
                return Storage::url($state);
               }
            })
            ->autoplay()    
            ->icon('hugeicons-file-view')
            ->preload(false);


            $fileUploadActions[] = Action::make('process_document')
            
            
            ->label('Process Document')
            ->icon('ri-ai-generate-2')
            ->action(function(Get $get, Set $set,$livewire){
                $file = $get('file_url');
                $fileUrl = $file[array_key_first($file)];
                $filePath= 'app/private/livewire-tmp/' . $fileUrl->getFilename();
                // dd($filePath);
                try{
                    $billAiController = new BillAiController();
                    $document = $billAiController->processDocument($filePath, false);
                    // dd($document);
                    $jsonData = json_decode($document->serializeToJsonString(),true)['entities'];
                    $entities = [];
                    foreach ($jsonData as $entity) {
                        if (isset($entity['pageAnchor']['pageRefs'][0]['boundingPoly']['normalizedVertices'])) {
                            $vertices = $entity['pageAnchor']['pageRefs'][0]['boundingPoly']['normalizedVertices'];
                            $entities[] = [
                                'type' => $entity['type'],
                                'mentionText' => $entity['mentionText'],
                                'confidence' => $entity['confidence'],
                                'vertices' => $vertices,
                            ];
                        }
                    }
            
                    $parsedImage = new ParsedImage($document);
                    $dataForFrontend = $parsedImage->toJsonSerializable();

                    
                    $set('bill_number', $parsedImage->getInvoiceId());
                    $set('bill_date', $parsedImage->getInvoiceDate());
                    $set('line_items', $parsedImage->getLineItems());
                    $set('generated_data',$dataForFrontend);
                    $set('data_for_img', $dataForFrontend);


                    $entities = json_encode($entities);
                    
                    
                }catch(\Exception $e){
                    dd($e);
                }
            });
        

        
         }
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
                
                FileUpload::make('file_url')
                ->hintActions($fileUploadActions)
                ->storeFiles(true)
                ->directory('bills')
                ->visibility('private')
                ->label('PDF File')
                ->acceptedFileTypes(['application/pdf'])
                ->multiple(false)
                ->openable(true)
                ->downloadable(true)
                ->columnSpanFull()
                ->reactive()
                ->required(),
                Forms\Components\Hidden::make('generated_data'),

               
            
            Section::make('Data from AI')
            ->hidden(fn (Get $get) => $get('generated_data') === null)
            ->schema([
                DisplayDocAi::make('data_for_img')
                
                ->reactive(),
            ]),
        
            Section::make('Line Items')
            ->hidden(fn (Get $get) => $get('generated_data') === null)
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
         return $form->schema($fields);
         

    
            
       
    }


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


    protected function extractDataFromDocument($fileUrl, $provider_id){
        try{
        $billAiController = new BillAiController();
        $document = $billAiController->processDocument($fileUrl, false);
        $jsonData = json_decode($document->serializeToJsonString(),true)['entities'];
        $entities = [];
        foreach ($jsonData as $entity) {
            if (isset($entity['pageAnchor']['pageRefs'][0]['boundingPoly']['normalizedVertices'])) {
                $vertices = $entity['pageAnchor']['pageRefs'][0]['boundingPoly']['normalizedVertices'];
                $entities[] = [
                    'type' => $entity['type'],
                    'mentionText' => $entity['mentionText'],
                    'confidence' => $entity['confidence'],
                    'vertices' => $vertices,
                ];
            }
        }

        $parsedImage = new ParsedImage($document);
        $dataForFrontend = $parsedImage->toJsonSerializable();
        return $dataForFrontend;
    }catch(\Exception $e){
        dd($e);
    }
}

    
}
