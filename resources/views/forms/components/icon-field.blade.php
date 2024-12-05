@assets
<!-- load tailwind cdn  -->
{{-- <script src="https://cdn.tailwindcss.com"></script>
 --}}@endassets

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        {{-- if ($field->getState() == true <x-heroicon-o-check-circle /> else <x-heroicon-o-x-circle />  --}}
        @if ($field->getState())
            {{-- <x-heroicon-o-check-circle class="success-icon " /> --}}
            <!-- badge -->
            <span class="flex items-center gap-2 w-28 bg-green-200 text-green-800 px-2 py-1 rounded-md text-md font-semibold">
                <span class="w-2 h-2 bg-green-500 rounded-full mr-1"></span>
                Success
            </span>
        @else
            <!-- badge -->
            <span class="flex items-center gap-2 w-28 bg-red-200 text-red-800 px-2 py-1 rounded-md text-md font-semibold">
                <span class="w-2 h-2 bg-red-500 rounded-full mr-1"></span>
                Failed
            </span>
            {{-- <x-heroicon-o-x-circle class="error-icon " /> --}}
        @endif
        


{{--         <x-dynamic-component style="height: {{ $field->getSize() }}px; width: {{ $field->getSize() }}px; color: {{ $field->getColor() }};" :component="$field->getIcon()" />
 --}}
    </div>
</x-dynamic-component>
