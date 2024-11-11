<?php
 
namespace App\Filament\Pages\Auth;

use App\Enums\RoleUserEnum;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Joaopaulolndev\FilamentEditProfile\Pages\EditProfilePage;

class EditProfile extends EditProfilePage
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getNameFormComponent(),
                $this->getEmailFormComponent(),
                $this->getPasswordFormComponent(),
                $this->getPasswordConfirmationFormComponent(),
                $this->getRoleFormComponent(),

            ]);
    }
    
    protected function getRoleFormComponent(): Component
    {
        return Select::make('role')
            ->options(RoleUserEnum::class)
            ->native(false)
            ->enum(RoleUserEnum::class)
            ->disabled();
    }
}