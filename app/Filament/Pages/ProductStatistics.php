<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductLineChart;
use App\Filament\Widgets\ProductYearSelector;
use App\Livewire\ProductChartFilter;
use Filament\Pages\Page;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Request;
use Livewire\Component;

class ProductStatistics extends Page
{
    public $filterData = [];
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.product-statistics';

    protected $listeners = ['filterUpdated' => 'updateChartData'];

    public function mount(HttpRequest $request): void
    {
       $reqs = $request->filterData;
       if($reqs){
        
        $this->filterData = $reqs;
        // dd($reqs);
       }
       
    }

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getWidgetData(): array
    {
        return [
            'filterData' => $this->filterData
        ];
    }

    protected function getFooterWidgets(): array
    {
        if($this->filterData){
        return [
            ProductLineChart::make(
                [
                    'filterData' => $this->filterData,
                ]
            ),
        ];
        }else{
            return [];
        }
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ProductChartFilter::make(
                [
                    'filterData' => $this->filterData,
                ]
            )
        ];
    }

    public function updateChartData(array $filterData): void
    {
        // Use the filter data to update the chart
        $this->filterData = $filterData;
        // Optionally, you can refresh the widget or perform other actions
        $this->dispatchBrowserEvent('chartDataUpdated');
        
    }
}
