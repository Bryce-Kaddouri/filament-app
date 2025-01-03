<?php

namespace App\Filament\Widgets;

use App\Models\LineItem;
use Filament\Support\RawJs;

use App\Models\Price;
use App\Models\Provider;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB as FacadesDB;

class ProviderBarChart extends ChartWidget
{
    protected static ?string $heading = 'Chart';


    protected function getData(): array
    {

        $activeFilter = $this->filter;

        $providers = Provider::with('prices')->get();
        $chartData = [
            
        ];

        foreach ($providers as $provider) {
            if($activeFilter == 'week'){
                $trend = Trend::query(
                    LineItem::query()
                    ->join('bills', 'line_items.bill_id', '=', 'bills.id')
                    ->where('bills.provider_id', '=', $provider->id)
                    )
                    
                    ->interval($activeFilter)
                    ->dateColumn('bills.bill_date')
                    ->between(
                        start: now()->startOfYear(),
                        end: now()->endOfYear(),
                    )
                    ->perWeek()
                    ->average('line_items.unit_price');
            }
            if($activeFilter == 'year'){
                $trend = Trend::query(
                    LineItem::query()
                    ->join('bills', 'line_items.bill_id', '=', 'bills.id')
                    ->where('bills.provider_id', '=', $provider->id)
                    )
                    
                    ->interval($activeFilter)
                    ->dateColumn('bills.bill_date')
                    ->between(
                        start: now()->startOfYear(),
                        end: now()->endOfYear(),
                    )
                    ->perYear()
                    ->average('line_items.unit_price');
            }else{
            $trend = Trend::query(
            LineItem::query()
            ->join('bills', 'line_items.bill_id', '=', 'bills.id')
                ->where('bills.provider_id', '=', $provider->id)
            )
            
            ->interval($activeFilter)
            ->dateColumn('bills.bill_date')
            ->between(
                start: now()->startOfYear(),
                end: now()->endOfYear(),
            )
            ->perMonth()
            ->average('line_items.unit_price');
        }
           
           

            $chartData[] = [
                'label' => $provider->name,
                'data' => $trend->map(fn (TrendValue $value) => $value->aggregate),
                'backgroundColor' => 'rgba(' . $provider->red . ', ' . $provider->green . ', ' . $provider->blue . ', 0.2)',
                'borderColor' => 'rgba(' . $provider->red . ', ' . $provider->green . ', ' . $provider->blue . ', 1)',
                'borderWidth' => 1, // Increased border width for better visibility
                'fill' => true,
                'skipNull' => true,
                
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
                        callback: (value) => '€' + value.toFixed(2),
                    },
                },
            },
        }
    JS);
}



}


