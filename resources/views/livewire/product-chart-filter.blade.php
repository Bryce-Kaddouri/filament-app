<div>
    <form wire:submit="filter">
        {{ $this->form }}

        <button type="submit">
            Submit
        </button>
    </form>

    <x-filament-actions::modals />
</div>
