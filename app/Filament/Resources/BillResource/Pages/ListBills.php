<?php

namespace App\Filament\Resources\BillResource\Pages;

use App\Filament\Resources\BillResource;
use App\Http\Controllers\BillAiController;
use App\Models\Bill;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\ListRecords;
use GuzzleHttp\Psr7\UploadedFile;
use Joaopaulolndev\FilamentPdfViewer\Forms\Components\PdfViewerField;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ListBills extends ListRecords
{
    protected static string $resource = BillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            // generate data with ai 
            // display a modal to upload pdf file
            Actions\Action::make('generate_data')
    ->form([
        Select::make('provider_id')
        ->relationship('provider', 'name')  
        ->searchable()
        ->preload()
        ->native(false)
        ->required(),
        FileUpload::make('file_url')
        ->storeFiles(true)
        ->directory('bills')
        ->visibility('public')
        ->afterStateUpdated(function (TemporaryUploadedFile $state, Set $set) {
            $tempPath = $state->getClientOriginalName();
            $set('temp_path', $tempPath);
        })
        ->label('PDF File')
        ->acceptedFileTypes(['application/pdf'])
        ->multiple(false)
        ->openable()
        ->downloadable()
        ->columnSpanFull()
        ->reactive()
        ->required(),
        Hidden::make('temp_path')
        ->default(function () {
            return request()->file('file_url');
        })
        
    ])
    ->action(function (array $data) {
        $fileUrl = $data['file_url'];
        $tempPath = $data['temp_path'];
        // dd($data);
        // dd($tempPath);
        // $tempRoute = route('temporary-file.serve', ['filename' => $tempPath]);
        // dd($tempRoute);
        
        /* $billAiController = new BillAiController();
        $document = $billAiController->processDocument($fileUrl, false); */
        // go to create bill page
        return redirect()->route('filament.admin.resources.bills.create', ['file_url' => $fileUrl, 'provider_id' => $data['provider_id']]);
    })

        ];
    }
}
