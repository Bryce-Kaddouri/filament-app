<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ProductYearSelector;
use Filament\Pages\Page;

class ProductStatistics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.product-statistics';

    public function getHeaderWidgetsColumns(): int | array
    {
        return 1;
    }

    protected function getHeaderWidgets(): array
    {
        return [
        ];
    }
}
