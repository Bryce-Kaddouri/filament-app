<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductLineChart;
use App\Filament\Widgets\ProductYearSelector;
use Filament\Pages\Page;

class ProductStatistics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.product-statistics';

    public function getFooterWidgetsColumns(): int | array
    {
        return 1;
    }

    public function getWidgetData(): array
{
    return [
        'stats' => [
            'total' => 100,
        ],
    ];
}

    protected function getFooterWidgets(): array
    {
        return [
            ProductLineChart::make(
                [
                    
                ]
            ),
        ];
    }
}
