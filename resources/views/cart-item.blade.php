<dl class="mt-1">
    @foreach($customFields as $name => $value)
        <div @class(['small d-flex gap-1', 'mb-1' => ! $loop->last])>
            <dt>{{ $name }}:</dt>
            <dd class="mb-0">{{ $value }}</dd>
        </div>
    @endforeach
</dl>
