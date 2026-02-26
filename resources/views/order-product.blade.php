<dl class="my-1">
    @foreach($customFieldValues as $customFieldValue)
        <div @class(['d-flex align-items-center gap-1', 'mb-1' => ! $loop->last])>
            <dt>{{ $customFieldValue->customField->label }}:</dt>
            <dd class="mb-0">
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
                            <img src="{{ $fileUrl }}" alt="{{ $customFieldValue->customField->label }}" class="rounded border" style="max-width: 40px; max-height: 40px; object-fit: cover;">
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
                    {{ $customFieldValue->value }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
