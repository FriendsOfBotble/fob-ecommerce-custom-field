@foreach($customFieldValues as $customFieldValue)
    <x-core::table.body.row>
        <x-core::table.body.cell>
            {{ $customFieldValue->customField->label }}
        </x-core::table.body.cell>
        <x-core::table.body.cell class="fw-bold">
            {{ $customFieldValue->value }}
        </x-core::table.body.cell>
    </x-core::table.body.row>
@endforeach
