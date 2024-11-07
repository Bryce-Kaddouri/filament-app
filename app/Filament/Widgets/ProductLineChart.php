<?php

namespace App\Filament\Widgets;
use Filament\Support\RawJs;

use App\Models\Price;
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

class ProductLineChart extends ChartWidget implements HasForms
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
            $product = $this->filterData['product'];
            $providers = $this->filterData['providers'];
            $effective_date_from = Carbon::createFromFormat('d/m/Y', explode(' - ', $this->filterData['effective_date_range'])[0]);
            $effective_date_to = Carbon::createFromFormat('d/m/Y', explode(' - ', $this->filterData['effective_date_range'])[1]);
            //dd($effective_date_from, $effective_date_to);
        }
        
        // dd($filterData, "test");
        $activeFilter = $this->filter;


        $providers = Provider::with('prices')->get();
        $chartData = [
            
        ];
        foreach ($providers as $provider) {
            if($activeFilter == 'week'){
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
                'backgroundColor' => 'rgba(' . $provider->red . ', ' . $provider->green . ', ' . $provider->blue . ', 0.2)',
                'borderColor' => 'rgba(' . $provider->red . ', ' . $provider->green . ', ' . $provider->blue . ', 1)',
                'borderWidth' => 2, // Increased border width for better visibility
                'tension' => 0.4, // Added tension for smoother curves
                'fill' => false,
                'stepped' => false, // Changed to false for a smoother line
                'stack' => 'stacked', // Added stacking for the dataset
                'spanGaps' => true,
            ];
        }

        $labels = array();
        if($activeFilter == 'month'){
            $labels = [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec',
            ];
        }else if($activeFilter == 'week'){
            $firstDayOfYear = now()->startOfYear();
            $lastDayOfYear = now()->endOfYear();
            $labels = [];
            while ($firstDayOfYear->lte($lastDayOfYear)) {
                // label must be from 2024-01-01 to 2024-01-07
                $labels[] = $firstDayOfYear->format('Y-m-d') . ' - ' . $firstDayOfYear->addWeek()->subDay()->format('Y-m-d');
                $firstDayOfYear->addWeek();
            }
        }else if($activeFilter == 'year'){
            $labels = [
                '2023',
                '2024',
            ];
        }
        // dd($distinctMonths);

 
    return [
        'datasets' => $chartData,        
        'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
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


