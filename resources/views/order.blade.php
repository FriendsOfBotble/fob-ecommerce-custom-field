@foreach($customFieldValues as $customFieldValue)
    <x-core::table.body.row>
        <x-core::table.body.cell>
            {{ $customFieldValue->customField->label }}
        </x-core::table.body.cell>
        <x-core::table.body.cell class="fw-bold">
            @php
                $fieldType = $customFieldValue->customField->type;
                $fieldValue = $customFieldValue->value;
            @endphp

            @if (in_array($fieldType, ['file', 'image']))
                <a href="{{ RvMedia::url($fieldValue) }}" target="_blank" download>{{ $customFieldValue->value }} <x-core::icon name="ti ti-download" /></a>
            @else
                {{ $customFieldValue->value }}
            @endif
        </x-core::table.body.cell>
    </x-core::table.body.row>
@endforeach
