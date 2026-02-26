<div class="bg-body-secondary rounded p-3 mt-3">
    <h6 class="mb-3">{{ __('Extra Information') }}</h6>

    @foreach($customFieldValues as $customFieldValue)
        @php
            $fieldType = $customFieldValue->customField->type;
            $fieldValue = $customFieldValue->value;
            $isFileType = in_array($fieldType, ['file', 'image']);
            $isImage = $isFileType && in_array(strtolower(pathinfo($fieldValue, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg']);
            $fileUrl = $isFileType ? RvMedia::url($fieldValue) : null;
        @endphp

        <div @class(['mb-3' => ! $loop->last, 'mb-0' => $loop->last])>
            <div class="text-muted small fw-medium mb-1">{{ $customFieldValue->customField->label }}</div>

            @if ($isImage)
                <a href="{{ $fileUrl }}" target="_blank" class="d-inline-block">
                    <img
                        src="{{ $fileUrl }}"
                        alt="{{ $customFieldValue->customField->label }}"
                        class="rounded border"
                        style="max-width: 120px; max-height: 120px; object-fit: cover;"
                    >
                </a>
            @elseif ($isFileType)
                <a href="{{ $fileUrl }}" target="_blank" download class="d-inline-flex align-items-center gap-2 text-decoration-none border rounded px-3 py-2 bg-white">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M12 17v-6"/><path d="M9.5 14.5l2.5 2.5l2.5 -2.5"/></svg>
                    <span class="small fw-medium">{{ basename($fieldValue) }}</span>
                </a>
            @else
                <div>{{ $fieldValue }}</div>
            @endif
        </div>
    @endforeach
</div>
