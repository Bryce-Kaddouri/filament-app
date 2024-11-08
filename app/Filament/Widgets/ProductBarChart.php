<?php

namespace App\Filament\Widgets;
use Filament\Support\RawJs;

use App\Models\Price;
use App\Models\Product;
use App\Models\Provider;
use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB as FacadesDB;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;

class ProductBarChart extends ChartWidget implements HasForms
{
    use InteractsWithForms;

    protected static ?string $heading = 'Chart';
    public $stats = [];


    public ?array $filterData = [];




    protected function getData(): array
    {
        $product = null;
        $providers = null;
        $effective_date_from = null;
        $effective_date_to = null;
        if($this->filterData){
            $product_id = $this->filterData['product'];
            $providers_id = $this->filterData['providers'];
            $effective_date_from = Carbon::createFromFormat('d/m/Y', explode(' - ', $this->filterData['effective_date_range'])[0]);
            $effective_date_to = Carbon::createFromFormat('d/m/Y', explode(' - ', $this->filterData['effective_date_range'])[1]);
            //dd($effective_date_from, $effective_date_to);
        }
        
        // dd($filterData, "test");
        $activeFilter = $this->filter;


        $product = Product::with('prices')->get();
        $chartData = [
            
        ];
        foreach ($providers_id as $provider_id) {
            $provider = Provider::find($provider_id);
            if($activeFilter == 'week'){
                $trend = Trend::query(
                    Price::query()
                        ->where('provider_id', $provider_id)
                        ->where('product_id', $product_id)
                        ->orderBy('effective_date', 'asc')
                    )
                    
                    ->interval($activeFilter)
                    ->dateColumn('effective_date')
                    ->between(
                        start: now()->startOfYear(),
                        end: now()->endOfYear(),
                    )
                    ->perWeek()
                    ->average('price');
            }else
            if($activeFilter == 'year'){
                $trend = Trend::query(
                    Price::query()
                        ->where('provider_id', $provider->id)
                    )
                    
                    ->interval($activeFilter)
                    ->dateColumn('effective_date')
                    ->between(
                        start: now()->startOfYear(),
                        end: now()->endOfYear(),
                    )
                    ->perYear()
                    ->average('price');
            }else{
                
            $trend = Trend::query(
            Price::query()
                ->where('provider_id', $provider->id)
            )
            
            ->interval($activeFilter)
            ->dateColumn('effective_date')
            ->between(
                start: $effective_date_from ?? now()->startOfYear(),
                end: $effective_date_to ?? now()->endOfYear(),
            )
            ->perMonth()
            ->average('price');
        }
           
           

            $chartData[] = [
                'label' => $provider->name,
                'data' => $trend->map(fn (TrendValue $value) => $value->aggregate),
                'backgroundColor' => 'rgba(' . $provider->red . ', ' . $provider->green . ', ' . $provider->blue . ', 1)',
                'borderColor' => 'rgba(' . $provider->red . ', ' . $provider->green . ', ' . $provider->blue . ', 1)',
                'borderWidth' => 1, // Increased border width for better visibility
                'fill' => true,
                'skipNull' => false,
            ];
        }

        $labels = array();
        if($activeFilter == 'month'){
            $labels = [
            ];
            // make sure that the effective_date_to is after effective_date_from

            if($effective_date_from->diffInMonths($effective_date_to) > 0){
            $start_month = $effective_date_from->format('m');
            $end_month = $effective_date_to->format('m');
            for($i = $start_month; $i <= $end_month; $i++){
                $date = Carbon::createFromFormat('m', $i)->format('M');
                
                $labels[] = $date;
            }
            }

        }else if($activeFilter == 'week'){
            

            $labels = [];
            if($effective_date_from->diffInWeeks($effective_date_to) > 0){
                /* $start_week = $effective_date_from->format('Y-m-d');
                $end_week = $effective_date_to->format('Y-m-d');
                $nb_weeks = $effective_date_from->diffInWeeks($effective_date_to); */
                // get the first monday <= effective_date_from
                $first_monday = $effective_date_from->startOfWeek();
                $last_monday = $effective_date_to->startOfWeek();
                $firstMondayBeforeFromOrEqual = $first_monday->isBefore($effective_date_from) || $first_monday->eq($effective_date_from);
                $lastMondayAfterToOrEqual = $last_monday->isAfter($effective_date_to) || $last_monday->eq($effective_date_to);
                $nb_weeks = $first_monday->diffInWeeks($last_monday);
                for($i = 0; $i <= $nb_weeks; $i++){
                    $newDate = $first_monday->copy();
                    $labels[] = $newDate->format('Y-m-d') . ' - ' . $newDate->addWeek()->subDay()->format('Y-m-d');
                    // check if year changed
                    
                    $first_monday->addWeek();
                    
                } 
            }
        }else if($activeFilter == 'year'){
            $labels = [
                
            ];
            if($effective_date_from->diffInYears($effective_date_to) > 0){
                $start_year = $effective_date_from->format('Y');
                $end_year = $effective_date_to->format('Y');
                for($i = $start_year; $i <= $end_year; $i++){
                    $labels[] = $i;
                }
            }
        }
        // dd($distinctMonths);

 
    return [
        'datasets' => $chartData,        
        'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    public ?string $filter = 'month';

    protected function getFilters(): ?array
{
    return [
        'week' => 'By week',
        'month' => 'By month',
        'year' => 'By year',
        
    ];
}

protected static ?array $options = [
    'plugins' => [
        'legend' => [
            'display' => true,
        ],
    ],
];

public function getDescription(): ?string
{
        return 'The number of blog posts published per month.';
    }


    protected function getOptions(): RawJs
{
    return RawJs::make(<<<JS
        {
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value) => '€' + value,
                    },
                },
            },
        }
    JS);
}



}


