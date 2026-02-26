@foreach($customFieldValues as $customFieldValue)
    <p class="mb-1">
        @php
            $fieldType = $customFieldValue->customField->type;
            $fieldValue = $customFieldValue->value;
            $isFileType = in_array($fieldType, ['file', 'image']);
            $isImage = $isFileType && in_array(strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
            $fileUrl = $isFileType ? RvMedia::url($fieldValue) : null;
        @endphp

        {{ $customFieldValue->customField->label }}:
        @if ($isImage)
            <a href="{{ $fileUrl }}" target="_blank">
                <img src="{{ $fileUrl }}" alt="{{ $customFieldValue->customField->label }}" class="rounded border" style="max-width: 80px; max-height: 80px; object-fit: cover;">
            </a>
        @elseif ($isFileType)
            <a href="{{ $fileUrl }}" target="_blank" download>
                {{ basename($fieldValue) }}
            </a>
        @else
            <strong>{{ $fieldValue }}</strong>
        @endif
    </p>
@endforeach
