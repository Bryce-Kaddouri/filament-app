<div>
<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{ state: $wire.$entangle('{{ $getStatePath() }}') }">
        <div style="position: relative; width: 100%; height: 400px;">
        
        @foreach($field->getState() as $image)

            <div  style="background-image: url('data:{{ $image['mime_type'] }};base64,{{ $image['content_encoded'] }}'); width:100%; height: 100%; position: relative; object-fit: contain;" alt="Image">
            {{-- {{dd($image['supplier_name_points'])}} --}}
            <div :style="`left: 50px; top: 50px; background-color: red; width: 100px; height: 50px; position: relative; z-index: 10`"></div>
            {{-- @for($i=0; $i < count($image['supplier_name_points']); $i++)
                <div :style="`left: 50px; top: 50px; background-color: red; width: 100px; height: 50px; position: relative; z-index: 10`"></div>
                @endfor
            </div> --}}
        @endforeach
        </div>
        <!-- Interact with the `state` property in Alpine.js -->
       {{--  <input x-model="state" /> --}}

    </div>
</x-dynamic-component>
</div>