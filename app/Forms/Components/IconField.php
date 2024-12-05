<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Mockery\Matcher\Closure;

class IconField extends Field
{
    protected string $view = 'forms.components.icon-field';

    protected string | Closure $icon;
    protected string|Closure $color;

    protected int | Closure $size = 50;


    
}
