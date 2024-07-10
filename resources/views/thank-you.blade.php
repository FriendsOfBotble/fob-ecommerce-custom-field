<dl class="bg-body-secondary rounded p-2 mt-3">
    <h6 class="mb-2">{{ __('Extra Information') }}</h6>

    @foreach($customFieldValues as $customFieldValue)
        <div @class(['d-flex align-items-center', 'mb-1' => ! $loop->last])>
            <dt class="d-inline-block">{{ $customFieldValue->customField->label }}:</dt>
            <dd class="order-customer-info-meta mb-0">{{ $customFieldValue->value }}</dd>
        </div>
    @endforeach
</dl>
