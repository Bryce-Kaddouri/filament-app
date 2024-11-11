<?php

namespace App\Livewire;

use App\Enums\RoleUserEnum;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Livewire\Component;
use Illuminate\Contracts\View\View;
use Joaopaulolndev\FilamentEditProfile\Concerns\HasSort;

class CustomProfileComponent extends Component implements HasForms
{
    use InteractsWithForms;
    use HasSort;

    public ?array $data = [];

    public function __construct()
    {
        // dd(auth()->guard('web')->user()->role);
        $this->data['role'] = auth()->guard('web')->user()->role;
        // dd(auth()->guard('web')->user()->role);
    }

    protected static int $sort = 0;

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        // dd(auth()->guard('web')->user()->role);
        
        return $form
            ->schema([
                Section::make('Role Information')
                    ->aside()
                    ->description('Role Information')
                    ->schema([
                        Select::make('role')
                            ->options(RoleUserEnum::class)
                            ->native(false)
                            ->enum(RoleUserEnum::class)
                            ->disabled()
                            ->default($this->data['role'])
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
    }

    public function render(): View
    {
        return view('livewire.custom-profile-component');
    }
}
