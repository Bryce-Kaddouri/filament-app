<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Resources\ProjectResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProjects extends ViewRecord
{
    protected static string $resource = ProjectResource::class;

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->form->fill([
            'name' => $this->record->name,
            'projectId' => $this->record->projectId,
            'projectNumber' => $this->record->projectNumber,
            'createTime' => $this->record->createTime,
            'firebase' => $this->record->firebase,
            'enabledServices' => $this->record->getEnabledServices(),
        ]);
        // $this->data = $this->data['enabledServices'];
         // dd($this->data);
        // dd($this->record->getEnabledServices());
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
