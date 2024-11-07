<?php

namespace App\Livewire;

use App\Models\Price;
use App\Models\Product;
use App\Models\Provider;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;
use Filament\Forms\Components\Actions\Button;
use Filament\Widgets\Widget;

class ProductChartFilter extends Widget implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public ?array $filterData = [];

    public function mount(): void
    {
        //dd($this->filterData);
        if($this->filterData){
         $this->form->fill(
             [
                'product' => $this->filterData['product'],
                'providers' => $this->filterData['providers'],
                'effective_date_range' => "01/01/2024 - 31/12/2024",
            ] 
         );
        }else{
            $this->form->fill();
        }
    }

    

    public function form(Form $form): Form
    {     
        return $form
            ->schema([    
                    Forms\Components\Select::make('product')
                ->native(false)
                ->searchable()
                ->placeholder('Select a product')
                ->relationship('product', 'name')
                ->preload()
                ->required()
                ->reactive()
                ->afterStateUpdated(function (callable $set, $state) {
                    // Reset the provider field when product changes
                    $providersIds = Provider::whereHas('products', fn ($query) => $query->where('product_id', $state))->pluck('id')->toArray();
                    // dd($providersIds);
                    $set('providers', $providersIds);
                }),

                Forms\Components\Select::make('providers')
                ->native(false)
                ->searchable()
                ->placeholder('Select a provider')
                ->multiple()
                ->relationship('provider', 'name', function ($query, $get) {
                    // Filter providers based on selected product_id
                    if ($get('product')) {
                        return $query->whereHas('products', function ($query) use ($get) {
                            $query->where('product_id', $get('product'));
                        });
                    }
                    return $query;
                })
               
                ->preload()
                ->required()
                ->disabled(fn ($get) => !$get('product')) // Disable if product is not selected
                ->reactive(),
                DateRangePicker::make('effective_date_range')
                    ->label('Effective Date Range')
                    ->defaultThisYear()
                    ->required(),
                    
                
            ])
            
            ->statePath('data')
            ->model(Price::class);
    }

    public function filter(): void
    {
        $data = $this->form->getState();
        //dd($data);
        // redirect to the product statistics page with the filter data
        redirect()->route('filament.admin.pages.product-statistics', ['filterData' => $data]);
        
    }

    public function render(): View
    {
        return view('livewire.product-chart-filter');
    }
}