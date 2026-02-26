@foreach($customFieldValues as $customFieldValue)
    <x-core::table.body.row>
        <x-core::table.body.cell>
            {{ $customFieldValue->customField->label }}
        </x-core::table.body.cell>
        <x-core::table.body.cell>
            @php
                $fieldType = $customFieldValue->customField->type;
                $fieldValue = $customFieldValue->value;
                $isFileType = in_array($fieldType, ['file', 'image']);
                $isImage = $isFileType && in_array(strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
                $fileUrl = $isFileType ? RvMedia::url($fieldValue) : null;
            @endphp

            @if ($isImage)
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ $fileUrl }}" target="_blank">
                        <img src="{{ $fileUrl }}" alt="{{ $customFieldValue->customField->label }}" class="rounded border" style="max-width: 48px; max-height: 48px; object-fit: cover;">
                    </a>
                    <a href="{{ $fileUrl }}" target="_blank" download class="small">
                        <x-core::icon name="ti ti-download" /> {{ basename($fieldValue) }}
                    </a>
                </div>
            @elseif ($isFileType)
                <a href="{{ $fileUrl }}" target="_blank" download class="d-inline-flex align-items-center gap-1">
                    <x-core::icon name="ti ti-file" /> {{ basename($fieldValue) }} <x-core::icon name="ti ti-download" class="ms-1" />
                </a>
            @else
                <span class="fw-bold">{{ $fieldValue }}</span>
            @endif
        </x-core::table.body.cell>
    </x-core::table.body.row>
@endforeach
