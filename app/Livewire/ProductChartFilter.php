<?php

namespace App\Livewire;

use App\Models\Price;
use App\Models\Product;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;


class ProductChartFilter extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {

        $this->form->fill();
    }

    

    public function form(Form $form): Form
    {

   
        
        

        
        if(isset($this->data['providers']) && count($this->data['providers']) > 0){
            // dd($this->data);
        }
       
         
        return $form
            ->schema([
                /* Forms\Components\Select::make('product')
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->reactive()
                    
                    ->afterStateUpdated(function (callable $set, $state) {
                        // Reset the product field when provider changes
                        // $set('providers', null);
                        
                       
                        $set('providers', null);
                    })
                    ->options(Product::all()->pluck('name', 'id')), */
                /* Forms\Components\Select::make('providers')
                    ->required()
                    ->native(false)
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->reactive()
                   
                   
                    ->options(function () {
                        if (!$this->data['product']) {
                            return [];
                        }


                        $query = Provider::whereHas('products', function ($query) {
                            $query
                            ->where('product_id', $this->data['product']);
                            
                        });

                       

                        // Exclude currently selected providers
                        
                        return $query->pluck('name', 'id');
                    })
                    ->disabled(fn ($get) => !$get('product')), */ // Disable if product not selected

                    
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
        dd($data);

        /* $record = Price::create($data);

        $this->form->model($record)->saveRelationships(); */
    }

    public function render(): View
    {
        return view('livewire.product-chart-filter');
    }
}