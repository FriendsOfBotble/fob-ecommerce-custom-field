<dl class="bg-body-secondary rounded p-2 mt-3">
    <h6 class="mb-2">{{ __('Extra Information') }}</h6>

    @foreach($customFieldValues as $customFieldValue)
        <div @class(['d-flex align-items-center', 'mb-1' => ! $loop->last])>
            <dt class="d-inline-block">{{ $customFieldValue->customField->label }}:</dt>
            <dd class="order-customer-info-meta mb-0">
                @php
                    $fieldType = $customFieldValue->customField->type;
                    $fieldValue = $customFieldValue->value;
                @endphp

                @if (in_array($fieldType, ['file', 'image']))
                    <a href="{{ RvMedia::url($fieldValue) }}" target="_blank">{{ $customFieldValue->value }}</a>
                @else
                    {{ $customFieldValue->value }}
                @endif
            </dd>
        </div>
    @endforeach
</dl>
