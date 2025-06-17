<dl class="my-1">
    @foreach($customFieldValues as $customFieldValue)
        <div @class(['d-flex align-items-center gap-1', 'mb-1' => ! $loop->last])>
            <dt>{{ $customFieldValue->customField->label }}:</dt>
            <dd class="mb-0">
                @php
                    $fieldType = $customFieldValue->customField->type;
                    $fieldValue = $customFieldValue->value;
                @endphp

                @if (in_array($fieldType, ['file', 'image']))
                    <a href="{{ RvMedia::url($fieldValue) }}" target="_blank" download>{{ $customFieldValue->value }} <x-core::icon name="ti ti-download" /></a>
                @else
                    {{ $customFieldValue->value }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
