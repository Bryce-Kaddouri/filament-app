<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProviderBarChart;
use App\Filament\Widgets\ProviderLineChart;
use Filament\Pages\Page;

class ProviderStatistics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.provider-statistics';

    public function getHeaderWidgetsColumns(): int | array
{
    return 1;
}

    protected function getHeaderWidgets(): array
    {
        return [
            ProviderLineChart::class,
            ProviderBarChart::class,
        ];
    }
}
