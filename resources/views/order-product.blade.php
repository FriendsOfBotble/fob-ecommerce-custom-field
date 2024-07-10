<dl class="my-1">
    @foreach($customFieldValues as $customFieldValue)
        <div @class(['d-flex align-items-center gap-1', 'mb-1' => ! $loop->last])>
            <dt>{{ $customFieldValue->customField->label }}:</dt>
            <dd class="mb-0">{{ $customFieldValue->value }}</dd>
        </div>
    @endforeach
</dl>
